@extends('layouts.app')

@section('title', 'Security Devices')

@section('content')

<div class="w-full max-w-5xl mx-auto mt-8">

    <!-- Header -->

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">

        <div>

            <p class="text-sm font-semibold text-indigo-600 uppercase tracking-wider">
                WebAuthn Security
            </p>

            <h1 class="text-3xl font-bold text-slate-900 mt-1">
                Security Devices
            </h1>

            <p class="text-slate-500 mt-1">
                Manage the passkeys and authenticators connected to your account.
            </p>

        </div>


        <div class="flex gap-3">

            <a
                href="{{ route('dashboard') }}"
                class="px-4 py-2.5 border border-slate-300 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-50"
            >
                ← Dashboard
            </a>

            <a
                href="{{ route('security.activity') }}"
                class="px-4 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700"
            >
                Security Activity
            </a>

        </div>

    </div>


    <!-- Success/Error -->

    @if(session('success'))

        <div class="mb-6 p-4 bg-green-50 text-green-700 rounded-xl border border-green-200 font-medium">
            ✅ {{ session('success') }}
        </div>

    @endif


    @if(session('error'))

        <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-xl border border-red-200 font-medium">
            ⚠️ {{ session('error') }}
        </div>

    @endif


    <!-- Devices -->

    @if($devices->count())

        <div class="space-y-5">

            @foreach($devices as $device)

                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">

                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">

                        <!-- Device Info -->

                        <div class="flex items-start gap-4">

                            <div class="w-14 h-14 bg-indigo-100 rounded-2xl flex items-center justify-center text-2xl">
                                @if($device->device_type === 'phone')
                                    📱
                                @elseif($device->device_type === 'tablet')
                                    📱
                                @elseif($device->device_type === 'computer')
                                    💻
                                @elseif($device->device_type === 'security_key')
                                    🔑
                                @else
                                    🔐
                                @endif
                            </div>


                            <div>

                                <div class="flex flex-wrap items-center gap-2">

                                    <h2 class="text-lg font-bold text-slate-900">
                                        {{ $device->name ?? 'Passkey Device' }}
                                    </h2>

                                    @if($device->last_used_at)

                                        <span class="px-2.5 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold">
                                            Recently Used
                                        </span>

                                    @endif

                                </div>


                                <p class="text-sm text-slate-500 mt-1">
                                    {{ $device->getDeviceTypeLabel() }}
                                </p>


                                <p class="text-xs text-slate-400 mt-2">
                                    Registered
                                    {{ $device->created_at?->format('M d, Y h:i A') ?? 'Unknown' }}
                                </p>

                            </div>

                        </div>


                        <!-- Actions -->

                        <div class="flex flex-wrap gap-2">

                            <button
                                type="button"
                                onclick="openRenameModal({{ $device->id }}, @js($device->name ?? 'Passkey Device'))"
                                class="px-4 py-2 rounded-lg border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                            >
                                ✏️ Rename
                            </button>


                            <form
                                action="{{ route('webauthn.devices.delete', $device->id) }}"
                                method="POST"
                                onsubmit="return confirm('Are you sure you want to remove this security device?')"
                            >

                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="px-4 py-2 rounded-lg bg-red-50 text-red-600 border border-red-200 text-sm font-semibold hover:bg-red-100"
                                >
                                    🗑 Remove
                                </button>

                            </form>

                        </div>

                    </div>


                    <!-- Device Details -->

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-6 pt-6 border-t border-slate-100">

                        <div>

                            <p class="text-xs uppercase font-bold text-slate-400">
                                Operating System
                            </p>

                            <p class="text-sm font-semibold text-slate-700 mt-1">
                                {{ $device->device_os ?? 'Unknown' }}
                            </p>

                        </div>


                        <div>

                            <p class="text-xs uppercase font-bold text-slate-400">
                                IP Address
                            </p>

                            <p class="text-sm font-semibold text-slate-700 mt-1">
                                {{ $device->last_ip ?? 'Unknown' }}
                            </p>

                        </div>


                        <div>

                            <p class="text-xs uppercase font-bold text-slate-400">
                                Last Used
                            </p>

                            <p class="text-sm font-semibold text-slate-700 mt-1">

                                @if($device->last_used_at)
                                    {{ $device->last_used_at->diffForHumans() }}
                                @else
                                    Never
                                @endif

                            </p>

                        </div>


                        <div>

                            <p class="text-xs uppercase font-bold text-slate-400">
                                Sign Count
                            </p>

                            <p class="text-sm font-semibold text-slate-700 mt-1">
                                {{ $device->sign_count }}
                            </p>

                        </div>

                    </div>


                    <!-- Transport -->

                    <div class="mt-5">

                        <p class="text-xs uppercase font-bold text-slate-400 mb-2">
                            Authentication Transport
                        </p>

                        <span class="inline-flex px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 text-sm font-medium">
                            {{ $device->getTransportsList() }}
                        </span>

                    </div>

                </div>

            @endforeach

        </div>

    @else

        <div class="bg-white border border-dashed border-slate-300 rounded-2xl p-12 text-center">

            <div class="text-5xl mb-5">
                🔐
            </div>

            <h2 class="text-xl font-bold text-slate-800">
                No security devices registered
            </h2>

            <p class="text-slate-500 mt-2">
                Add a passkey or security key from your dashboard.
            </p>

            <a
                href="{{ route('dashboard') }}"
                class="inline-block mt-6 px-5 py-3 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-700"
            >
                Go to Dashboard
            </a>

        </div>

    @endif

</div>


<!-- Rename Modal -->

<div
    id="renameModal"
    class="hidden fixed inset-0 z-50 bg-black/50 items-center justify-center p-4"
>

    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">

        <h2 class="text-xl font-bold text-slate-900">
            Rename Security Device
        </h2>

        <p class="text-sm text-slate-500 mt-1 mb-6">
            Give this passkey a name that helps you identify it.
        </p>


        <form
            id="renameForm"
            method="POST"
        >

            @csrf
            @method('PUT')


            <label class="block text-sm font-semibold text-slate-700 mb-2">
                Device Name
            </label>

            <input
                type="text"
                id="renameInput"
                name="name"
                required
                maxlength="255"
                class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
            >


            <div class="flex justify-end gap-3 mt-6">

                <button
                    type="button"
                    onclick="closeRenameModal()"
                    class="px-4 py-2.5 border border-slate-300 rounded-xl font-semibold text-slate-700 hover:bg-slate-50"
                >
                    Cancel
                </button>


                <button
                    type="submit"
                    class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-700"
                >
                    Save Name
                </button>

            </div>

        </form>

    </div>

</div>


<script>

    function openRenameModal(id, name) {

        const modal = document.getElementById('renameModal');
        const form = document.getElementById('renameForm');
        const input = document.getElementById('renameInput');

        form.action = `/webauthn/devices/${id}`;

        input.value = name;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        input.focus();
    }


    function closeRenameModal() {

        const modal = document.getElementById('renameModal');

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }


    document
        .getElementById('renameModal')
        .addEventListener('click', function(event) {

            if (event.target === this) {
                closeRenameModal();
            }

        });

</script>

@endsection