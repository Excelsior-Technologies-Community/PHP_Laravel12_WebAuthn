@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="mx-auto max-w-md">
    <!-- Header -->
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-slate-900 mb-2">Create Account</h1>
        <p class="text-slate-600">Join and start using passwordless authentication</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
        <!-- Form -->
        <form action="{{ route('register.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Name Field -->
            <div>
                <label for="name" class="block text-sm font-semibold text-slate-900 mb-2">
                    Full Name
                </label>
                <input
                    type="text"
                    name="name"
                    id="name"
                    value="{{ old('name') }}"
                    placeholder="John Doe"
                    class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition @error('name') border-red-500 @enderror"
                    required
                >
                @error('name')
                    <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email Field -->
            <div>
                <label for="email" class="block text-sm font-semibold text-slate-900 mb-2">
                    Email Address
                </label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    value="{{ old('email') }}"
                    placeholder="you@example.com"
                    class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition @error('email') border-red-500 @enderror"
                    required
                >
                @error('email')
                    <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Field -->
            <div>
                <label for="password" class="block text-sm font-semibold text-slate-900 mb-2">
                    Password
                </label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    placeholder="Minimum 8 characters"
                    class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition @error('password') border-red-500 @enderror"
                    required
                >
                @error('password')
                    <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm Password Field -->
            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-slate-900 mb-2">
                    Confirm Password
                </label>
                <input
                    type="password"
                    name="password_confirmation"
                    id="password_confirmation"
                    placeholder="Confirm your password"
                    class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition @error('password_confirmation') border-red-500 @enderror"
                    required
                >
                @error('password_confirmation')
                    <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-lg transition duration-200 flex items-center justify-center space-x-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <span>Create Account</span>
            </button>
        </form>

        <!-- Divider -->
        <div class="relative my-8">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-slate-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white text-slate-600">Already have an account?</span>
            </div>
        </div>

        <!-- Login Link -->
        <a
            href="{{ route('login') }}"
            class="w-full block text-center py-2.5 border border-slate-300 text-slate-700 font-semibold rounded-lg hover:bg-slate-50 transition"
        >
            Sign In Instead
        </a>
    </div>

    <!-- Footer Text -->
    <p class="mt-6 text-center text-sm text-slate-600">
        By creating an account, you agree to our <a href="#" class="text-indigo-600 hover:text-indigo-700 font-medium">Terms</a> and <a href="#" class="text-indigo-600 hover:text-indigo-700 font-medium">Privacy Policy</a>
    </p>
</div>
@endsection