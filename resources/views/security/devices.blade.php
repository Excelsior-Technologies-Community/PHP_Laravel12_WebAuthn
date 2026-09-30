@extends('layouts.app')

@section('title', 'Security Devices')

@section('content')

<div class="w-full max-w-7xl mx-auto mt-8">

    {{-- HEADER --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5 mb-8">

        <div>
            <p class="text-sm font-semibold text-indigo-600 uppercase tracking-wider">
                WebAuthn Security
            </p>

            <h1 class="text-3xl font-bold text-slate-900 mt-1">
                Security Devices
            </h1>

            <p class="text-slate-500 mt-1">
                Search, filter and manage your registered passkeys.
            </p>
        </div>

        <div class="flex flex-wrap gap-3">

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

            <a
                href="{{ route('webauthn.devices.export', request()->query()) }}"
                class="px-4 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-semibold hover:bg-emerald-700"
            >
                ⬇ Export CSV
            </a>

        </div>

    </div>


    {{-- SUCCESS --}}
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 text-green-700 rounded-xl border border-green-200">
            ✅ {{ session('success') }}
        </div>
    @endif


    {{-- ERROR --}}
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-xl border border-red-200">
            ⚠️ {{ session('error') }}
        </div>
    @endif


    {{-- STATISTICS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <p class="text-sm text-slate-500">
                Total Devices
            </p>

            <p class="text-3xl font-bold text-slate-900 mt-2">
                {{ $totalDevices }}
            </p>
        </div>


        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <p class="text-sm text-slate-500">
                Recently Used
            </p>

            <p class="text-3xl font-bold text-green-600 mt-2">
                {{ $recentDevices }}
            </p>

            <p class="text-xs text-slate-400 mt-1">
                Used during last 30 days
            </p>
        </div>


        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <p class="text-sm text-slate-500">
                Never Used
            </p>

            <p class="text-3xl font-bold text-amber-600 mt-2">
                {{ $neverUsedDevices }}
            </p>
        </div>


        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <p class="text-sm text-slate-500">
                Inactive
            </p>

            <p class="text-3xl font-bold text-red-600 mt-2">
                {{ $inactiveDevices }}
            </p>

            <p class="text-xs text-slate-400 mt-1">
                Never used or inactive 30+ days
            </p>
        </div>

    </div>


    {{-- FILTERS --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm mb-8">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-5">

            <div>
                <h2 class="text-lg font-bold text-slate-900">
                    Search & Filters
                </h2>

                <p class="text-sm text-slate-500">
                    Find a specific security device.
                </p>
            </div>

            <a
                href="{{ route('webauthn.devices.list') }}"
                class="text-sm font-semibold text-indigo-600 hover:text-indigo-700"
            >
                Reset Filters
            </a>

        </div>


        <form
            method="GET"
            action="{{ route('webauthn.devices.list') }}"
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4"
        >

            {{-- SEARCH --}}
            <div class="lg:col-span-2">

                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Search
                </label>

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Device, OS, type or RP ID..."
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 outline-none"
                >

            </div>


            {{-- TYPE --}}
            <div>

                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Device Type
                </label>

                <select
                    name="device_type"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300"
                >

                    <option value="">
                        All Types
                    </option>

                    @foreach($deviceTypes as $type)

                        <option
                            value="{{ $type }}"
                            @selected(request('device_type') === $type)
                        >
                            {{ ucwords(str_replace('_', ' ', $type)) }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- OS --}}
            <div>

                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Operating System
                </label>

                <select
                    name="device_os"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300"
                >

                    <option value="">
                        All OS
                    </option>

                    @foreach($deviceOs as $os)

                        <option
                            value="{{ $os }}"
                            @selected(request('device_os') === $os)
                        >
                            {{ $os }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- USAGE --}}
            <div>

                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Usage
                </label>

                <select
                    name="usage"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300"
                >

                    <option value="">
                        All Devices
                    </option>

                    <option
                        value="recent"
                        @selected(request('usage') === 'recent')
                    >
                        Recently Used
                    </option>

                    <option
                        value="never"
                        @selected(request('usage') === 'never')
                    >
                        Never Used
                    </option>

                    <option
                        value="inactive"
                        @selected(request('usage') === 'inactive')
                    >
                        Inactive
                    </option>

                </select>

            </div>


            {{-- SORT --}}
            <div>

                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Sort
                </label>

                <select
                    name="sort"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300"
                >

                    <option value="newest" @selected(request('sort', 'newest') === 'newest')>
                        Newest
                    </option>

                    <option value="oldest" @selected(request('sort') === 'oldest')>
                        Oldest
                    </option>

                    <option value="name_asc" @selected(request('sort') === 'name_asc')>
                        Name A-Z
                    </option>

                    <option value="name_desc" @selected(request('sort') === 'name_desc')>
                        Name Z-A
                    </option>

                    <option value="last_used" @selected(request('sort') === 'last_used')>
                        Recently Used
                    </option>

                    <option value="least_used" @selected(request('sort') === 'least_used')>
                        Least Used
                    </option>

                    <option value="score" @selected(request('sort') === 'score')>
                        Security Score
                    </option>

                </select>

            </div>


            <div class="lg:col-span-6 flex justify-end">

                <button
                    type="submit"
                    class="px-6 py-3 bg-slate-900 text-white rounded-xl font-semibold hover:bg-slate-800"
                >
                    🔎 Apply Filters
                </button>

            </div>

        </form>

    </div>


    {{-- DEVICE LIST --}}
    @if($devices->count())

        <div class="space-y-5">

            @foreach($devices as $device)

                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">

                    <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-6">

                        {{-- DEVICE INFORMATION --}}
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
                                        {{ $device->alias ?: ($device->name ?: 'Passkey Device') }}
                                    </h2>

                                    @if($device->last_used_at)

                                        <span class="px-2.5 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold">
                                            Recently Used
                                        </span>

                                    @else

                                        <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-bold">
                                            Never Used
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


                        {{-- ACTIONS --}}
                        <div class="flex flex-wrap gap-2">

                            <button
                                type="button"
                                onclick="openRenameModal(
                                    {{ $device->id }},
                                    @js($device->alias ?: ($device->name ?? 'Passkey Device'))
                                )"
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


                    {{-- DETAILS --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mt-6 pt-6 border-t border-slate-100">

                        <div>
                            <p class="text-xs uppercase font-bold text-slate-400">
                                Operating System
                            </p>

                            <p class="text-sm font-semibold text-slate-700 mt-1">
                                {{ $device->device_os ?: 'Unknown' }}
                            </p>
                        </div>


                        <div>
                            <p class="text-xs uppercase font-bold text-slate-400">
                                Last Used
                            </p>

                            <p class="text-sm font-semibold text-slate-700 mt-1">
                                {{ $device->last_used_at?->diffForHumans() ?? 'Never' }}
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


                        <div>
                            <p class="text-xs uppercase font-bold text-slate-400">
                                Security Score
                            </p>

                            <p class="text-sm font-bold mt-1
                                @if($device->security_score >= 90)
                                    text-green-600
                                @elseif($device->security_score >= 70)
                                    text-amber-600
                                @else
                                    text-red-600
                                @endif
                            ">
                                {{ $device->security_score }}/100
                            </p>
                        </div>


                        <div>
                            <p class="text-xs uppercase font-bold text-slate-400">
                                RP ID
                            </p>

                            <p class="text-sm font-semibold text-slate-700 mt-1 truncate">
                                {{ $device->rp_id ?: 'Unknown' }}
                            </p>
                        </div>

                    </div>


                    {{-- TRANSPORT --}}
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


        {{-- PAGINATION --}}
        <div class="mt-6 bg-white border border-slate-200 rounded-2xl p-5">
            {{ $devices->links() }}
        </div>

    @else

        <div class="bg-white border border-dashed border-slate-300 rounded-2xl p-12 text-center">

            <div class="text-5xl mb-5">
                🔐
            </div>

            <h2 class="text-xl font-bold text-slate-800">
                No security devices found
            </h2>

            <p class="text-slate-500 mt-2">
                Try changing your search or filters.
            </p>

        </div>

    @endif

</div>


{{-- RENAME MODAL --}}
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
                class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 outline-none"
            >


            <div class="flex justify-end gap-3 mt-6">

                <button
                    type="button"
                    onclick="closeRenameModal()"
                    class="px-4 py-2.5 border border-slate-300 rounded-xl font-semibold text-slate-700"
                >
                    Cancel
                </button>


                <button
                    type="submit"
                    class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl font-semibold"
                >
                    Save Name
                </button>

            </div>

        </form>

    </div>

</div>


<script>

function openRenameModal(id, name)
{
    const modal = document.getElementById('renameModal');
    const form = document.getElementById('renameForm');
    const input = document.getElementById('renameInput');

    form.action = `/webauthn/devices/${id}`;

    input.value = name;

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    input.focus();
}


function closeRenameModal()
{
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