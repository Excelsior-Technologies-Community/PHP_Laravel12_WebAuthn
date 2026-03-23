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
     * Get options for WebAuthn registration
     */
    public function registerOptions(Request $request)
    {
        try {
            $user = Auth::user();

            // Generate cryptographically secure challenge
            $challenge = bin2hex(random_bytes(32));
            $userId = (string)$user->id;
            $userName = $user->email;
            $userDisplayName = $user->name;

            // Get RP ID and handle localhost/IP addresses
            $host = parse_url(config('app.url'), PHP_URL_HOST);
            
            // WebAuthn doesn't accept bare IP addresses, convert to localhost for development
            if ($host === '127.0.0.1' || $host === '0.0.0.0' || $host === '::1') {
                $rpId = 'localhost';
            } else {
                $rpId = $host;
            }
            
            $rpName = config('app.name', 'WebAuthn');

            $options = [
                'challenge' => base64_encode(hex2bin($challenge)),
                'rp' => [
                    'name' => $rpName,
                    'id' => $rpId
                ],
                'user' => [
                    'id' => base64_encode($userId),
                    'name' => $userName,
                    'displayName' => $userDisplayName,
                ],
                'pubKeyCredParams' => [
                    ['alg' => -7, 'type' => 'public-key'],   // ES256
                    ['alg' => -257, 'type' => 'public-key'], // RS256
                ],
                'timeout' => 60000,
                'attestation' => 'direct',
                'userVerification' => 'preferred',
                'residentKey' => 'preferred',
            ];

            // Store options in session for verification
            session([
                'webauthn_registration_options' => $options,
                'webauthn_registration_challenge' => $challenge,
                'webauthn_registration_user_id' => $userId,
                'webauthn_registration_rp_id' => $rpId,
            ]);

            Log::info('WebAuthn registration options generated', [
                'user_id' => $user->id,
                'rp_id' => $rpId,
                'original_host' => $host,
            ]);

            return response()->json($options);

        } catch (\Exception $e) {
            Log::error('WebAuthn registration error', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate registration options: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Register a new WebAuthn credential
     */
    public function register(Request $request)
    {
        try {
            $user = Auth::user();

            // Validate input
            $validated = $request->validate([
                'id' => 'required|string',
                'rawId' => 'required|string',
                'response.attestationObject' => 'required|string',
                'response.clientDataJSON' => 'required|string',
                'deviceName' => 'nullable|string|max:255',
            ]);

            $credentialId = $validated['id'];
            $deviceName = $validated['deviceName'] ?? 'My Device';

            // Security check: Verify credential doesn't already exist
            if (WebauthnKey::where('credential_id', $credentialId)->exists()) {
                Log::warning('Duplicate credential registration attempt', [
                    'user_id' => $user->id,
                    'credential_id' => substr($credentialId, 0, 20),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'This credential is already registered'
                ], 400);
            }

            // Verify attestation object format
            $attestationObject = $validated['response']['attestationObject'] ?? '';
            if (empty($attestationObject) || !$this->isValidBase64($attestationObject)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid attestation object format'
                ], 400);
            }

            // Get device info
            $userAgent = $request->userAgent();
            $deviceType = $this->detectDeviceType($userAgent);
            $rpId = session('webauthn_registration_rp_id', 'localhost');

            // Store credential
            $credential = $user->webauthnKeys()->create([
                'name' => $deviceName,
                'credential_id' => $credentialId,
                'credential_public_key' => $validated['response']['clientDataJSON'],
                'transports' => $request->input('transports', []),
                'sign_count' => 0,
                'rp_id' => $rpId,
                'origin' => $request->getHttpHost(),
                'device_type' => $deviceType,
                'device_os' => $this->detectOS($userAgent),
                'last_ip' => $request->ip(),
                'last_user_agent' => $userAgent,
            ]);

            Log::info('WebAuthn credential registered successfully', [
                'user_id' => $user->id,
                'credential_id' => substr($credentialId, 0, 20),
                'device_type' => $deviceType,
            ]);

            // Clear session
            session()->forget([
                'webauthn_registration_options',
                'webauthn_registration_challenge',
                'webauthn_registration_user_id',
                'webauthn_registration_rp_id'
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
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('WebAuthn registration error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get options for WebAuthn login
     */
    public function loginOptions(Request $request)
    {
        try {
            $email = $request->input('email', '');
            $challenge = bin2hex(random_bytes(32));
            
            // Get RP ID and handle localhost/IP addresses
            $host = parse_url(config('app.url'), PHP_URL_HOST);
            
            if ($host === '127.0.0.1' || $host === '0.0.0.0' || $host === '::1') {
                $rpId = 'localhost';
            } else {
                $rpId = $host;
            }

            $allowCredentials = [];

            // If email provided, fetch user's credentials
            if (!empty($email)) {
                $user = User::where('email', $email)->first();

                if (!$user) {
                    Log::warning('Login attempt with non-existent email', [
                        'email' => $email,
                        'ip' => $request->ip(),
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'User not found'
                    ], 404);
                }

                // Get active credentials only
                $allowCredentials = $user->webauthnKeys()
                    ->whereNull('deleted_at')
                    ->get()
                    ->map(function ($key) {
                        return [
                            'id' => base64_encode($key->credential_id),
                            'type' => 'public-key',
                            'transports' => $key->transports ?? []
                        ];
                    })
                    ->toArray();
            }

            $options = [
                'challenge' => base64_encode(hex2bin($challenge)),
                'timeout' => 60000,
                'rpId' => $rpId,
                'userVerification' => 'preferred',
                'allowCredentials' => $allowCredentials,
            ];

            // Store in session for verification
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
                'message' => 'Failed to get login options'
            ], 400);
        }
    }

    /**
     * Authenticate with WebAuthn
     */
    public function login(Request $request)
    {
        try {
            $credentialId = $request->input('id');
            $newSignCount = (int)$request->input('response.signCount', 0);

            // Find the credential
            $webauthnKey = WebauthnKey::whereNull('deleted_at')
                ->where('credential_id', $credentialId)
                ->first();

            if (!$webauthnKey) {
                Log::warning('Login with unregistered credential', [
                    'credential_id' => substr($credentialId, 0, 20),
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Credential not found. Please register first.'
                ], 404);
            }

            // Security check: Verify sign count to prevent cloning
            if ($newSignCount <= $webauthnKey->sign_count) {
                Log::error('Possible cloned authenticator detected', [
                    'user_id' => $webauthnKey->user_id,
                    'credential_id' => substr($credentialId, 0, 20),
                    'old_sign_count' => $webauthnKey->sign_count,
                    'new_sign_count' => $newSignCount,
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid authentication attempt'
                ], 403);
            }

            $user = $webauthnKey->user;

            // Update sign count and last used
            $webauthnKey->update([
                'sign_count' => $newSignCount,
                'last_ip' => $request->ip(),
                'last_user_agent' => $request->userAgent(),
                'last_used_at' => now(),
            ]);

            // Log the user in
            Auth::login($user);
            $request->session()->regenerate();

            // Clear session
            session()->forget([
                'webauthn_login_options',
                'webauthn_login_challenge',
                'webauthn_login_rp_id'
            ]);

            Log::info('WebAuthn login successful', [
                'user_id' => $user->id,
                'credential_id' => substr($credentialId, 0, 20),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Authentication successful',
                'redirect' => route('dashboard')
            ]);

        } catch (\Exception $e) {
            Log::error('WebAuthn login error', [
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Authentication failed'
            ], 400);
        }
    }

    /**
     * List all registered WebAuthn devices
     */
    public function listDevices()
    {
        $user = Auth::user();
        $devices = $user->webauthnKeys()->whereNull('deleted_at')->get();

        return view('webauthn.devices', [
            'devices' => $devices
        ]);
    }

    /**
     * Delete a WebAuthn device
     */
    public function deleteDevice($id)
    {
        try {
            $user = Auth::user();
            $device = $user->webauthnKeys()->findOrFail($id);

            // Soft delete
            $device->delete();

            Log::info('WebAuthn device deleted', [
                'user_id' => $user->id,
                'device_id' => $id,
            ]);

            return redirect()->back()
                ->with('success', 'Device removed securely.');

        } catch (\Exception $e) {
            Log::error('WebAuthn device deletion error', [
                'user_id' => Auth::id(),
                'device_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to delete device');
        }
    }

    /**
     * Helper: Validate Base64 string
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
     * Helper: Detect device type from user agent
     */
    private function detectDeviceType(string $userAgent): string
    {
        if (strpos($userAgent, 'Mobile') !== false || strpos($userAgent, 'Android') !== false) {
            return 'phone';
        }
        if (strpos($userAgent, 'Tablet') !== false || strpos($userAgent, 'iPad') !== false) {
            return 'tablet';
        }
        if (strpos($userAgent, 'Windows') !== false) {
            return 'laptop';
        }
        if (strpos($userAgent, 'Mac') !== false) {
            return 'laptop';
        }

        return 'unknown';
    }

    /**
     * Helper: Detect OS from user agent
     */
    private function detectOS(string $userAgent): string
    {
        if (strpos($userAgent, 'Windows') !== false) return 'Windows';
        if (strpos($userAgent, 'Mac') !== false) return 'macOS';
        if (strpos($userAgent, 'Linux') !== false) return 'Linux';
        if (strpos($userAgent, 'Android') !== false) return 'Android';
        if (strpos($userAgent, 'iOS') !== false) return 'iOS';
        if (strpos($userAgent, 'iPhone') !== false) return 'iOS';
        if (strpos($userAgent, 'iPad') !== false) return 'iPadOS';

        return 'Unknown';
    }
}