@extends('layouts.app')
@section('title', 'Login - Anvilpat')

@section('content')
<div class="w-full max-w-md mx-auto mt-12">
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-slate-800">Welcome Back</h2>
        <p class="text-slate-500 text-sm mt-1">Sign in to your account</p>
    </div>

    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
        <div class="flex border-b border-slate-100 bg-slate-50/50 p-1">
            <button onclick="switchTab('email')" id="tab-email" class="flex-1 py-3 text-sm font-semibold rounded-xl text-slate-500 hover:text-slate-800 transition-all">Email & Password</button>
            <button onclick="switchTab('biometric')" id="tab-biometric" class="flex-1 py-3 text-sm font-semibold rounded-xl bg-white text-blue-600 shadow-sm transition-all">Biometric / Key</button>
        </div>

        <div class="p-8">
            @if($errors->any())
                <div class="mb-4 p-3 bg-red-50 text-red-600 text-sm rounded-lg font-medium border border-red-100">
                    {{ $errors->first() }}
                </div>
            @endif

            <form id="form-email" action="{{ route('login.store') }}" method="POST" class="hidden space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                    <input type="email" name="email" id="login-email" required class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" placeholder="you@email.com">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                    <input type="password" name="password" required class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" placeholder="••••••••">
                </div>
                <button type="submit" class="w-full py-3 bg-slate-800 hover:bg-slate-900 text-white font-semibold rounded-xl transition-all">Sign In</button>
            </form>

            <div id="form-biometric" class="text-center">
                <div class="w-24 h-24 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-6 border-4 border-white shadow-sm">
                    <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4" /></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-2">Biometric Unlock</h3>
                <p class="text-sm text-slate-500 mb-6">Type your email in the Email tab, then click scan below.</p>
                
                <button onclick="startBiometricLogin()" id="btn-biometric" class="w-full py-3.5 bg-slate-800 hover:bg-slate-900 text-white font-semibold rounded-xl transition-all flex items-center justify-center gap-2 shadow-lg shadow-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    Scan & Login
                </button>
                <div id="biometric-status" class="hidden mt-4 p-3 bg-green-50 text-green-700 text-sm rounded-lg font-medium border border-green-200">
                    ✅ Verified! Redirecting...
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function switchTab(tab) {
        if(tab === 'email') {
            document.getElementById('form-email').classList.remove('hidden');
            document.getElementById('form-biometric').classList.add('hidden');
            document.getElementById('tab-email').classList.add('bg-white', 'text-blue-600', 'shadow-sm');
            document.getElementById('tab-email').classList.remove('text-slate-500');
            document.getElementById('tab-biometric').classList.remove('bg-white', 'text-blue-600', 'shadow-sm');
            document.getElementById('tab-biometric').classList.add('text-slate-500');
        } else {
            document.getElementById('form-email').classList.add('hidden');
            document.getElementById('form-biometric').classList.remove('hidden');
            document.getElementById('tab-biometric').classList.add('bg-white', 'text-blue-600', 'shadow-sm');
            document.getElementById('tab-biometric').classList.remove('text-slate-500');
            document.getElementById('tab-email').classList.remove('bg-white', 'text-blue-600', 'shadow-sm');
            document.getElementById('tab-email').classList.add('text-slate-500');
        }
    }

    async function startBiometricLogin() {
        const btn = document.getElementById('btn-biometric');
        const status = document.getElementById('biometric-status');
        
        // 👉 Get email from input
        const emailInput = document.getElementById('login-email').value;
        
        btn.innerHTML = '<span class="animate-pulse">Waiting for scan...</span>';
        btn.classList.add('opacity-75', 'cursor-not-allowed');

        try {
            const webauthn = new Webauthn();
            
            // Pass email to package login function
            let loginData = {};
            if (emailInput) {
                loginData = { email: emailInput };
            }
            
            await webauthn.login(loginData);
            
            // ✅ Success
            btn.classList.add('hidden');
            status.classList.remove('hidden');
            setTimeout(() => { window.location.href = "{{ route('dashboard') }}"; }, 1000);
            
        } catch (error) {
            console.error("Login Error:", error);
            if (!emailInput) {
                 alert("Bhai, pehla 'Email & Password' tab ma tamaru Email type karo pachi Scan dabavo.");
            } else {
                 alert("Login Failed. Tamaru fingerprint match nathi thatu.");
            }
            btn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg> Scan & Login';
            btn.classList.remove('opacity-75', 'cursor-not-allowed');
        }
    }
</script>
@endsection