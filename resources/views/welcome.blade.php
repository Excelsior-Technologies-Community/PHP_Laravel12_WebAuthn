@extends('layouts.app')

@section('title', 'Welcome')

@section('content')
<div class="max-w-6xl">
    <!-- Hero Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center py-12">
        <!-- Left Content -->
        <div>
            <div class="inline-block bg-indigo-100 rounded-full px-4 py-2 mb-6">
                <span class="text-sm font-semibold text-indigo-700">🔐 Modern Authentication</span>
            </div>

            <h1 class="text-5xl font-bold text-slate-900 leading-tight mb-6">
                Go Passwordless with WebAuthn
            </h1>

            <p class="text-xl text-slate-600 mb-8">
                Experience the future of authentication with passkeys and security keys. Faster, safer, and easier than passwords.
            </p>

            <div class="flex gap-4 mb-12">
                @auth
                    <a href="{{ route('dashboard') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-8 py-4 rounded-xl transition inline-flex items-center space-x-2">
                        <span>Go to Dashboard</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @else
                    <a href="{{ route('register') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-8 py-4 rounded-xl transition inline-flex items-center space-x-2">
                        <span>Get Started</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                    <a href="{{ route('login') }}" class="border-2 border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold px-8 py-4 rounded-xl transition">
                        Sign In
                    </a>
                @endauth
            </div>

            <!-- Trust Badges -->
            <div class="flex items-center space-x-6">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span class="text-sm text-slate-600">Phishing Resistant</span>
                </div>
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span class="text-sm text-slate-600">Industry Standard</span>
                </div>
            </div>
        </div>

        <!-- Right Illustration -->
        <div class="relative">
            <div class="bg-gradient-to-br from-indigo-100 to-blue-100 rounded-2xl p-12 text-center">
                <svg class="w-32 h-32 mx-auto text-indigo-600 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <p class="text-indigo-900 font-semibold">Secure by Default</p>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 py-12">
        <!-- Feature 1 -->
        <div class="bg-white rounded-xl border border-slate-200 p-8">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-2">Lightning Fast</h3>
            <p class="text-slate-600">Sign in with a single tap. No need to remember complex passwords.</p>
        </div>

        <!-- Feature 2 -->
        <div class="bg-white rounded-xl border border-slate-200 p-8">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m7 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-2">Phishing Proof</h3>
            <p class="text-slate-600">Cryptographic protection ensures your account stays safe from attackers.</p>
        </div>

        <!-- Feature 3 -->
        <div class="bg-white rounded-xl border border-slate-200 p-8">
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-2">Works Everywhere</h3>
            <p class="text-slate-600">Use your passkey across multiple devices and platforms seamlessly.</p>
        </div>
    </div>

    <!-- How It Works -->
    <div class="bg-gradient-to-r from-slate-50 to-slate-100 rounded-2xl p-12 my-12">
        <h2 class="text-3xl font-bold text-slate-900 mb-8 text-center">How It Works</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Step 1 -->
            <div class="text-center">
                <div class="w-16 h-16 bg-indigo-600 text-white rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-bold">1</div>
                <h3 class="text-lg font-semibold text-slate-900 mb-2">Create Account</h3>
                <p class="text-slate-600">Sign up with your email and create a secure passkey</p>
            </div>

            <!-- Arrow -->
            <div class="hidden md:flex items-center justify-center">
                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </div>

            <!-- Step 2 -->
            <div class="text-center">
                <div class="w-16 h-16 bg-indigo-600 text-white rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-bold">2</div>
                <h3 class="text-lg font-semibold text-slate-900 mb-2">Register Devices</h3>
                <p class="text-slate-600">Add your security keys or fingerprint to your account</p>
            </div>

            <!-- Arrow -->
            <div class="hidden md:flex items-center justify-center">
                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </div>

            <!-- Step 3 -->
            <div class="text-center">
                <div class="w-16 h-16 bg-indigo-600 text-white rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-bold">3</div>
                <h3 class="text-lg font-semibold text-slate-900 mb-2">Sign In Securely</h3>
                <p class="text-slate-600">Use your passkey to authenticate safely and quickly</p>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="bg-gradient-to-r from-indigo-600 to-blue-600 rounded-2xl p-12 text-center text-white my-12">
        <h2 class="text-4xl font-bold mb-4">Ready to Go Passwordless?</h2>
        <p class="text-xl mb-8 text-indigo-100">Join thousands of users who have switched to passwordless authentication.</p>
        @guest
            <div class="flex gap-4 justify-center">
                <a href="{{ route('register') }}" class="bg-white text-indigo-600 hover:bg-indigo-50 font-semibold px-8 py-4 rounded-xl transition">
                    Get Started Free
                </a>
                <a href="{{ route('login') }}" class="border-2 border-white hover:bg-white/10 text-white font-semibold px-8 py-4 rounded-xl transition">
                    Sign In
                </a>
            </div>
        @endguest
    </div>
</div>
@endsection