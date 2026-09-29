<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WebauthnKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WebauthnController extends Controller
{
    /**
     * Generate WebAuthn registration options.
     */
    public function registerOptions(Request $request)
    {
        try {
            $user = Auth::user();

            $challenge = bin2hex(random_bytes(32));
            $userId = (string) $user->id;
            $userName = $user->email;
            $userDisplayName = $user->name;

            $host = parse_url(config('app.url'), PHP_URL_HOST);

            if (
                $host === '127.0.0.1' ||
                $host === '0.0.0.0' ||
                $host === '::1'
            ) {
                $rpId = 'localhost';
            } else {
                $rpId = $host;
            }

            $rpName = config('app.name', 'WebAuthn');

            $options = [
                'challenge' => base64_encode(hex2bin($challenge)),

                'rp' => [
                    'name' => $rpName,
                    'id' => $rpId,
                ],

                'user' => [
                    'id' => base64_encode($userId),
                    'name' => $userName,
                    'displayName' => $userDisplayName,
                ],

                'pubKeyCredParams' => [
                    [
                        'alg' => -7,
                        'type' => 'public-key',
                    ],
                    [
                        'alg' => -257,
                        'type' => 'public-key',
                    ],
                ],

                'timeout' => 60000,
                'attestation' => 'direct',
                'userVerification' => 'preferred',
                'residentKey' => 'preferred',
            ];

            session([
                'webauthn_registration_options' => $options,
                'webauthn_registration_challenge' => $challenge,
                'webauthn_registration_user_id' => $userId,
                'webauthn_registration_rp_id' => $rpId,
            ]);

            Log::info('WebAuthn registration options generated', [
                'user_id' => $user->id,
                'rp_id' => $rpId,
            ]);

            return response()->json($options);

        } catch (\Exception $e) {
            Log::error('WebAuthn registration error', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate registration options.',
            ], 400);
        }
    }

    /**
     * Register a new WebAuthn device.
     */
    public function register(Request $request)
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                'id' => 'required|string',
                'rawId' => 'required|string',
                'response.attestationObject' => 'required|string',
                'response.clientDataJSON' => 'required|string',
                'deviceName' => 'nullable|string|max:255',
            ]);

            $credentialId = $validated['id'];

            $deviceName = $validated['deviceName']
                ?? 'My Device';

            if (
                WebauthnKey::where('credential_id', $credentialId)
                    ->exists()
            ) {
                $this->recordActivity(
                    $user,
                    'device_registered',
                    'Attempted to register an already registered WebAuthn credential.',
                    'failed',
                    $request,
                    [
                        'credential_id' => substr($credentialId, 0, 20),
                    ]
                );

                return response()->json([
                    'success' => false,
                    'message' => 'This credential is already registered.',
                ], 400);
            }

            $attestationObject =
                $validated['response']['attestationObject'] ?? '';

            if (
                empty($attestationObject) ||
                !$this->isValidBase64($attestationObject)
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid attestation object format.',
                ], 400);
            }

            $userAgent = $request->userAgent() ?? '';

            $deviceType = $this->detectDeviceType($userAgent);
            $deviceOS = $this->detectOS($userAgent);

            $rpId = session(
                'webauthn_registration_rp_id',
                'localhost'
            );

            $credential = $user->webauthnKeys()->create([
                'name' => $deviceName,
                'credential_id' => $credentialId,

                'credential_public_key' =>
                    $validated['response']['clientDataJSON'],

                'transports' =>
                    $request->input('transports', []),

                'sign_count' => 0,
                'rp_id' => $rpId,
                'origin' => $request->getHttpHost(),
                'device_type' => $deviceType,
                'device_os' => $deviceOS,
                'last_ip' => $request->ip(),
                'last_user_agent' => $userAgent,
            ]);

            $this->recordActivity(
                $user,
                'device_registered',
                "WebAuthn device \"{$deviceName}\" was registered.",
                'success',
                $request,
                [
                    'device_id' => $credential->id,
                    'credential_id' => substr($credentialId, 0, 20),
                ]
            );

            session()->forget([
                'webauthn_registration_options',
                'webauthn_registration_challenge',
                'webauthn_registration_user_id',
                'webauthn_registration_rp_id',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Security device registered successfully!',
                'credentialId' => $credentialId,
                'device_id' => $credential->id,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('WebAuthn registration error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed.',
            ], 400);
        }
    }

    /**
     * Generate WebAuthn login options.
     */
    public function loginOptions(Request $request)
    {
        try {
            $email = $request->input('email', '');

            $challenge = bin2hex(random_bytes(32));

            $host = parse_url(config('app.url'), PHP_URL_HOST);

            if (
                $host === '127.0.0.1' ||
                $host === '0.0.0.0' ||
                $host === '::1'
            ) {
                $rpId = 'localhost';
            } else {
                $rpId = $host;
            }

            $allowCredentials = [];

            if (!empty($email)) {
                $user = User::where('email', $email)->first();

                if (!$user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User not found',
                    ], 404);
                }

                $allowCredentials = $user->webauthnKeys()
                    ->whereNull('deleted_at')
                    ->get()
                    ->map(function ($key) {
                        return [
                            'id' => base64_encode(
                                $key->credential_id
                            ),
                            'type' => 'public-key',
                            'transports' =>
                                $key->transports ?? [],
                        ];
                    })
                    ->toArray();
            }

            $options = [
                'challenge' =>
                    base64_encode(hex2bin($challenge)),

                'timeout' => 60000,

                'rpId' => $rpId,

                'userVerification' => 'preferred',

                'allowCredentials' => $allowCredentials,
            ];

            session([
                'webauthn_login_options' => $options,
                'webauthn_login_challenge' => $challenge,
                'webauthn_login_rp_id' => $rpId,
            ]);

            return response()->json($options);

        } catch (\Exception $e) {
            Log::error('WebAuthn login options error', [
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get login options.',
            ], 400);
        }
    }

    /**
     * Authenticate using WebAuthn.
     */
    public function login(Request $request)
    {
        try {
            $credentialId = $request->input('id');

            $newSignCount =
                (int) $request->input(
                    'response.signCount',
                    0
                );

            $webauthnKey = WebauthnKey::whereNull('deleted_at')
                ->where('credential_id', $credentialId)
                ->first();

            if (!$webauthnKey) {

                return response()->json([
                    'success' => false,
                    'message' =>
                        'Credential not found. Please register first.',
                ], 404);
            }

            if (
                $newSignCount <=
                $webauthnKey->sign_count
            ) {
                $this->recordActivity(
                    $webauthnKey->user,
                    'login_failed',
                    'WebAuthn authentication failed because the authenticator counter was invalid.',
                    'failed',
                    $request,
                    [
                        'credential_id' =>
                            substr($credentialId, 0, 20),

                        'old_sign_count' =>
                            $webauthnKey->sign_count,

                        'new_sign_count' =>
                            $newSignCount,
                    ]
                );

                Log::warning(
                    'Possible cloned authenticator detected',
                    [
                        'user_id' =>
                            $webauthnKey->user_id,

                        'credential_id' =>
                            substr($credentialId, 0, 20),

                        'old_sign_count' =>
                            $webauthnKey->sign_count,

                        'new_sign_count' =>
                            $newSignCount,

                        'ip' => $request->ip(),
                    ]
                );

                return response()->json([
                    'success' => false,
                    'message' =>
                        'Invalid authentication attempt.',
                ], 403);
            }

            $user = $webauthnKey->user;

            $webauthnKey->update([
                'sign_count' => $newSignCount,
                'last_ip' => $request->ip(),
                'last_user_agent' =>
                    $request->userAgent(),
                'last_used_at' => now(),
            ]);

            Auth::login($user);

            $request->session()->regenerate();

            $this->recordActivity(
                $user,
                'login_success',
                "Successfully authenticated using WebAuthn device \"{$webauthnKey->name}\".",
                'success',
                $request,
                [
                    'device_id' =>
                        $webauthnKey->id,

                    'credential_id' =>
                        substr($credentialId, 0, 20),

                    'sign_count' =>
                        $newSignCount,
                ]
            );

            session()->forget([
                'webauthn_login_options',
                'webauthn_login_challenge',
                'webauthn_login_rp_id',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Authentication successful',
                'redirect' => route('dashboard'),
            ]);

        } catch (\Exception $e) {
            Log::error('WebAuthn login error', [
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Authentication failed',
            ], 400);
        }
    }

    /**
     * List devices.
     */
    public function listDevices()
    {
        $user = Auth::user();

        $devices = $user->webauthnKeys()
            ->whereNull('deleted_at')
            ->latest()
            ->get();

        return view('webauthn.devices', [
            'devices' => $devices,
        ]);
    }

    /**
     * Rename a WebAuthn device.
     */
    public function renameDevice(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = Auth::user();

        $device = $user->webauthnKeys()
            ->whereNull('deleted_at')
            ->findOrFail($id);

        $oldName = $device->name;

        $device->update([
            'name' => $request->name,
        ]);

        $this->recordActivity(
            $user,
            'device_renamed',
            "WebAuthn device renamed from \"{$oldName}\" to \"{$request->name}\".",
            'success',
            $request,
            [
                'device_id' => $device->id,
                'old_name' => $oldName,
                'new_name' => $request->name,
            ]
        );

        return redirect()
            ->route('webauthn.devices.list')
            ->with(
                'success',
                'Device name updated successfully.'
            );
    }

    /**
     * Delete device.
     */
    public function deleteDevice($id)
    {
        try {
            $user = Auth::user();

            $device = $user->webauthnKeys()
                ->whereNull('deleted_at')
                ->findOrFail($id);

            $deviceName =
                $device->name ?? 'Passkey Device';

            $device->delete();

            $this->recordActivity(
                $user,
                'device_deleted',
                "WebAuthn device \"{$deviceName}\" was removed.",
                'success',
                request(),
                [
                    'device_id' => $id,
                ]
            );

            Log::info('WebAuthn device deleted', [
                'user_id' => $user->id,
                'device_id' => $id,
            ]);

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Device removed securely.'
                );

        } catch (\Exception $e) {
            Log::error(
                'WebAuthn device deletion error',
                [
                    'user_id' => Auth::id(),
                    'device_id' => $id,
                    'error' => $e->getMessage(),
                ]
            );

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Failed to delete device'
                );
        }
    }

    /**
     * Validate Base64.
     */
    private function isValidBase64(string $string): bool
    {
        if (!is_string($string)) {
            return false;
        }

        $decoded = @base64_decode($string, true);

        if ($decoded === false) {
            return false;
        }

        return base64_encode($decoded) === $string;
    }

    /**
     * Detect device type.
     */
    private function detectDeviceType(string $userAgent): string
    {
        if (
            strpos($userAgent, 'Mobile') !== false ||
            strpos($userAgent, 'Android') !== false
        ) {
            return 'phone';
        }

        if (
            strpos($userAgent, 'Tablet') !== false ||
            strpos($userAgent, 'iPad') !== false
        ) {
            return 'tablet';
        }

        if (
            strpos($userAgent, 'Windows') !== false ||
            strpos($userAgent, 'Mac') !== false
        ) {
            return 'computer';
        }

        return 'unknown';
    }

    /**
     * Detect operating system.
     */
    private function detectOS(string $userAgent): string
    {
        if (strpos($userAgent, 'Windows') !== false) {
            return 'Windows';
        }

        if (strpos($userAgent, 'Mac') !== false) {
            return 'macOS';
        }

        if (strpos($userAgent, 'Linux') !== false) {
            return 'Linux';
        }

        if (strpos($userAgent, 'Android') !== false) {
            return 'Android';
        }

        if (strpos($userAgent, 'iOS') !== false) {
            return 'iOS';
        }

        if (strpos($userAgent, 'iPhone') !== false) {
            return 'iOS';
        }

        if (strpos($userAgent, 'iPad') !== false) {
            return 'iPadOS';
        }

        return 'Unknown';
    }

    /**
     * Store security activity.
     */
    private function recordActivity(
        $user,
        string $activityType,
        string $description,
        string $status,
        Request $request,
        array $extra = []
    ): void {
        try {
            if (!$user) {
                return;
            }

            $user->securityActivities()->create([
                'activity_type' => $activityType,
                'description' => $description,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'device_name' => $extra['device_name'] ?? null,
                'device_type' =>
                    $this->detectDeviceType(
                        $request->userAgent() ?? ''
                    ),
                'device_os' =>
                    $this->detectOS(
                        $request->userAgent() ?? ''
                    ),
                'credential_id' =>
                    $extra['credential_id'] ?? null,
                'status' => $status,
                'metadata' => $extra,
            ]);
        } catch (\Throwable $e) {
            Log::warning(
                'Security activity logging failed',
                [
                    'user_id' => $user?->id,
                    'activity_type' => $activityType,
                    'error' => $e->getMessage(),
                ]
            );
        }
    }
}