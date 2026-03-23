@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="w-full max-w-3xl mx-auto mt-8">
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 text-green-700 rounded-xl font-medium border border-green-200 flex items-center gap-2">
            ✅ {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-8 border-b border-slate-100 flex items-center gap-6">
            <div class="w-20 h-20 bg-gradient-to-tr from-blue-600 to-indigo-600 text-white rounded-2xl flex items-center justify-center text-3xl font-bold shadow-lg shadow-blue-200">
                {{ substr(Auth::user()->name, 0, 1) }}
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-800">{{ Auth::user()->name }}</h1>
                <p class="text-slate-500">{{ Auth::user()->email }}</p>
            </div>
        </div>

        <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-5">
                <h3 class="text-lg font-bold text-slate-800 mb-4 border-b pb-2">Profile Details</h3>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Full Name</label>
                    <div class="px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 font-medium">{{ Auth::user()->name }}</div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Email Address</label>
                    <div class="px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 font-medium">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-bold text-slate-800 mb-4 border-b pb-2">Biometric Security</h3>
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl p-6 text-white relative overflow-hidden">
                    <div class="relative z-10">
                        <div class="flex items-center gap-3 mb-2">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            <h4 class="font-bold text-lg">Passkeys & Devices</h4>
                        </div>
                        <p class="text-sm text-slate-400 mb-6">Register your fingerprint or Windows Hello for faster, passwordless logins next time.</p>
                        
                        <button onclick="registerNewDevice()" id="btn-add-device" class="w-full py-3 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-xl transition-all shadow-lg shadow-blue-900/20 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Add Device Fingerprint
                        </button>
                    </div>
                </div>

                @if(auth()->user()->webauthnKeys && auth()->user()->webauthnKeys->count() > 0)
                <div class="mt-6">
                    <h4 class="text-xs font-bold text-slate-500 uppercase mb-3 tracking-wider">Registered Devices</h4>
                    <div class="space-y-3">
                        @foreach(auth()->user()->webauthnKeys as $device)
                        <div class="flex items-center justify-between p-3 border border-slate-200 rounded-xl bg-white shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center text-slate-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-700">{{ $device->name ?? 'Passkey Device' }}</p>
                                    <p class="text-xs text-slate-400">Added {{ $device->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                            <form action="{{ route('webauthn.devices.delete', $device->id) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" onclick="return confirm('Remove this device?')" class="p-2 text-slate-400 hover:text-red-500 transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div class="mt-6 text-center p-6 border border-dashed border-slate-300 rounded-xl bg-slate-50">
                     <p class="text-sm text-slate-500">No security devices registered yet.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    async function registerNewDevice() {
        const btn = document.getElementById('btn-add-device');
        const originalText = btn.innerHTML;
        
        btn.innerHTML = '<span class="animate-pulse">Scanning Fingerprint...</span>';
        btn.classList.add('opacity-75', 'cursor-not-allowed');

        try {
            // Using package's built in Webauthn object
            const webauthn = new Webauthn();
            await webauthn.register();
            
            alert("Success! Your device fingerprint is registered.");
            window.location.reload();
            
        } catch (error) {
            console.error("Registration Error:", error);
            alert("Registration failed. Are you using 127.0.0.1 instead of localhost?");
            btn.innerHTML = originalText;
            btn.classList.remove('opacity-75', 'cursor-not-allowed');
        }
    }
</script>
@endsection