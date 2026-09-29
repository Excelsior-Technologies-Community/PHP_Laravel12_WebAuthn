<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show registration form.
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle user registration.
     */
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
            ], [
                'name.required' => 'Name is required',
                'email.required' => 'Email is required',
                'email.email' => 'Invalid email format',
                'email.unique' => 'Email already registered',
                'password.required' => 'Password is required',
                'password.min' => 'Password must be at least 8 characters',
                'password.confirmed' => 'Passwords do not match',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            Auth::login($user);

            $this->recordActivity(
                $user,
                'password_login',
                'Account created and automatically signed in using password authentication.',
                'success',
                $request
            );

            return redirect()->route('dashboard')
                ->with('success', 'Account created successfully! Welcome ' . $user->name);

        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput($request->except('password', 'password_confirmation'));
        }
    }

    /**
     * Show login form.
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle user login.
     */
    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            if (Auth::attempt($credentials, $request->boolean('remember'))) {
                $request->session()->regenerate();

                $this->recordActivity(
                    Auth::user(),
                    'password_login',
                    'User successfully logged in using email and password.',
                    'success',
                    $request
                );

                return redirect()->route('dashboard')
                    ->with('success', 'Welcome back! You are now logged in.');
            }

            $user = User::where('email', $credentials['email'])->first();

            if ($user) {
                $this->recordActivity(
                    $user,
                    'login_failed',
                    'Failed password authentication attempt.',
                    'failed',
                    $request
                );
            }

            return back()
                ->withErrors([
                    'email' => 'The provided credentials do not match our records.'
                ])
                ->withInput($request->except('password'));

        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput($request->except('password'));
        }
    }

    /**
     * Show dashboard.
     */
    public function dashboard()
    {
        $user = Auth::user();

        $webauthnKeys = $user->webauthnKeys()
            ->whereNull('deleted_at')
            ->get();

        return view('dashboard', [
            'user' => $user,
            'webauthnKeys' => $webauthnKeys,
        ]);
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            $this->recordActivity(
                $user,
                'logout',
                'User logged out of the application.',
                'success',
                $request
            );
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'You have been logged out successfully.');
    }

    /**
     * Record security activity.
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
            $user->securityActivities()->create([
                'activity_type' => $activityType,
                'description' => $description,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'device_type' => $this->detectDeviceType($request->userAgent() ?? ''),
                'device_os' => $this->detectOS($request->userAgent() ?? ''),
                'status' => $status,
                'metadata' => $extra,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Unable to record security activity', [
                'user_id' => $user?->id,
                'activity_type' => $activityType,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function detectDeviceType(string $userAgent): string
    {
        if (
            str_contains($userAgent, 'Mobile') ||
            str_contains($userAgent, 'Android')
        ) {
            return 'phone';
        }

        if (
            str_contains($userAgent, 'Tablet') ||
            str_contains($userAgent, 'iPad')
        ) {
            return 'tablet';
        }

        if (
            str_contains($userAgent, 'Windows') ||
            str_contains($userAgent, 'Mac')
        ) {
            return 'computer';
        }

        return 'unknown';
    }

    private function detectOS(string $userAgent): string
    {
        if (str_contains($userAgent, 'Windows')) {
            return 'Windows';
        }

        if (str_contains($userAgent, 'Mac')) {
            return 'macOS';
        }

        if (str_contains($userAgent, 'Android')) {
            return 'Android';
        }

        if (
            str_contains($userAgent, 'iPhone') ||
            str_contains($userAgent, 'iPad')
        ) {
            return 'iOS';
        }

        if (str_contains($userAgent, 'Linux')) {
            return 'Linux';
        }

        return 'Unknown';
    }
}