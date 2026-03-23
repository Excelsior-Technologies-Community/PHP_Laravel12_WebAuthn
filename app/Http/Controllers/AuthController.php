<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show registration form
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle user registration
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

            return redirect()->route('dashboard')
                ->with('success', 'Account created successfully! Welcome ' . $user->name);

        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput($request->except('password', 'password_confirmation'));
        }
    }

    /**
     * Show login form
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle user login
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

                return redirect()->route('dashboard')
                    ->with('success', 'Welcome back! You are now logged in.');
            }

            return back()
                ->withErrors(['email' => 'The provided credentials do not match our records.'])
                ->withInput($request->except('password'));

        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput($request->except('password'));
        }
    }

    /**
     * Show dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        $webauthnKeys = $user->webauthnKeys()->whereNull('deleted_at')->get();

        return view('dashboard', [
            'user' => $user,
            'webauthnKeys' => $webauthnKeys,
        ]);
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'You have been logged out successfully.');
    }
}