@extends('layouts.app')

@section('title', 'Security Activity')

@section('content')

<div class="w-full max-w-6xl mx-auto mt-8">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">

        <div>
            <p class="text-sm font-semibold text-indigo-600 uppercase tracking-wider">
                Security Center
            </p>

            <h1 class="text-3xl font-bold text-slate-900 mt-1">
                Security Activity
            </h1>

            <p class="text-slate-500 mt-1">
                Monitor authentication events and registered security devices.
            </p>
        </div>

        <div class="flex gap-3">

            <a
                href="{{ route('dashboard') }}"
                class="px-4 py-2.5 border border-slate-300 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-50 transition"
            >
                ← Dashboard
            </a>

            <a
                href="{{ route('webauthn.devices.list') }}"
                class="px-4 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 transition"
            >
                Manage Devices
            </a>

        </div>

    </div>


    <!-- Statistics -->

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

        <!-- Total Activities -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm text-slate-500">
                        Total Activities
                    </p>

                    <p class="text-3xl font-bold text-slate-900 mt-2">
                        {{ $totalActivities }}
                    </p>
                </div>

                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    📊
                </div>

            </div>

        </div>


        <!-- Successful Logins -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm text-slate-500">
                        Successful Logins
                    </p>

                    <p class="text-3xl font-bold text-green-600 mt-2">
                        {{ $successfulLogins }}
                    </p>
                </div>

                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    ✅
                </div>

            </div>

        </div>


        <!-- Failed Attempts -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm text-slate-500">
                        Failed Attempts
                    </p>

                    <p class="text-3xl font-bold text-red-600 mt-2">
                        {{ $failedAttempts }}
                    </p>
                </div>

                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    ⚠️
                </div>

            </div>

        </div>


        <!-- Registered Devices -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm text-slate-500">
                        Active Devices
                    </p>

                    <p class="text-3xl font-bold text-indigo-600 mt-2">
                        {{ $registeredDevices }}
                    </p>
                </div>

                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                    🔐
                </div>

            </div>

        </div>

    </div>


    <!-- Additional Security Summary -->

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">

        <div class="bg-white border border-slate-200 rounded-2xl p-5">

            <p class="text-xs uppercase tracking-wider font-bold text-slate-400">
                Today's Activity
            </p>

            <p class="text-2xl font-bold text-slate-900 mt-2">
                {{ $todayActivities }}
            </p>

            <p class="text-sm text-slate-500 mt-1">
                Security events recorded today
            </p>

        </div>


        <div class="bg-white border border-slate-200 rounded-2xl p-5">

            <p class="text-xs uppercase tracking-wider font-bold text-slate-400">
                Today's Logins
            </p>

            <p class="text-2xl font-bold text-green-600 mt-2">
                {{ $todayLogins }}
            </p>

            <p class="text-sm text-slate-500 mt-1">
                Successful authentication events
            </p>

        </div>


        <div class="bg-white border border-slate-200 rounded-2xl p-5">

            <p class="text-xs uppercase tracking-wider font-bold text-slate-400">
                Failed Last 7 Days
            </p>

            <p class="text-2xl font-bold text-red-600 mt-2">
                {{ $recentFailures }}
            </p>

            <p class="text-sm text-slate-500 mt-1">
                Failed security attempts
            </p>

        </div>

    </div>


    <!-- Filters -->

    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm mb-6">

        <div class="flex items-center justify-between mb-5">

            <div>
                <h2 class="text-lg font-bold text-slate-900">
                    Activity Filters
                </h2>

                <p class="text-sm text-slate-500">
                    Filter your security history.
                </p>
            </div>

        </div>


        <form
            method="GET"
            action="{{ route('security.activity') }}"
            class="grid grid-cols-1 md:grid-cols-4 gap-4"
        >

            <!-- Activity -->
            <div>

                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Activity
                </label>

                <select
                    name="activity"
                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                >

                    <option value="">
                        All Activities
                    </option>

                    @foreach($activityTypes as $type)

                        <option
                            value="{{ $type }}"
                            @selected(request('activity') === $type)
                        >
                            {{ ucwords(str_replace('_', ' ', $type)) }}
                        </option>

                    @endforeach

                </select>

            </div>


            <!-- Status -->
            <div>

                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Status
                </label>

                <select
                    name="status"
                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                >

                    <option value="">
                        All Statuses
                    </option>

                    <option
                        value="success"
                        @selected(request('status') === 'success')
                    >
                        Success
                    </option>

                    <option
                        value="failed"
                        @selected(request('status') === 'failed')
                    >
                        Failed
                    </option>

                    <option
                        value="warning"
                        @selected(request('status') === 'warning')
                    >
                        Warning
                    </option>

                </select>

            </div>


            <!-- Date -->
            <div>

                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Date
                </label>

                <input
                    type="date"
                    name="date"
                    value="{{ request('date') }}"
                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                >

            </div>


            <!-- Buttons -->
            <div class="flex items-end gap-2">

                <button
                    type="submit"
                    class="flex-1 bg-slate-800 hover:bg-slate-900 text-white rounded-xl px-4 py-2.5 font-semibold transition"
                >
                    Filter
                </button>

                <a
                    href="{{ route('security.activity') }}"
                    class="px-4 py-2.5 border border-slate-300 rounded-xl text-slate-700 font-semibold hover:bg-slate-50 transition"
                >
                    Reset
                </a>

            </div>

        </form>

    </div>


    <!-- Activity Table -->

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">

        <div class="p-6 border-b border-slate-200">

            <h2 class="text-lg font-bold text-slate-900">
                Security Activity History
            </h2>

            <p class="text-sm text-slate-500 mt-1">
                Authentication and device-management events.
            </p>

        </div>


        @if($activities->count())

            <div class="overflow-x-auto">

                <table class="w-full">

                    <thead class="bg-slate-50">

                        <tr>

                            <th class="text-left px-6 py-4 text-xs font-bold text-slate-500 uppercase">
                                Activity
                            </th>

                            <th class="text-left px-6 py-4 text-xs font-bold text-slate-500 uppercase">
                                Device
                            </th>

                            <th class="text-left px-6 py-4 text-xs font-bold text-slate-500 uppercase">
                                Location / IP
                            </th>

                            <th class="text-left px-6 py-4 text-xs font-bold text-slate-500 uppercase">
                                Status
                            </th>

                            <th class="text-left px-6 py-4 text-xs font-bold text-slate-500 uppercase">
                                Time
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-slate-100">

                        @foreach($activities as $activity)

                            <tr class="hover:bg-slate-50 transition">

                                <td class="px-6 py-4">

                                    <div class="font-semibold text-slate-800">
                                        {{ $activity->activity_label }}
                                    </div>

                                    <div class="text-xs text-slate-500 mt-1 max-w-sm">
                                        {{ $activity->description }}
                                    </div>

                                </td>


                                <td class="px-6 py-4">

                                    <div class="text-sm font-medium text-slate-700">
                                        {{ $activity->device_name ?? 'Browser Session' }}
                                    </div>

                                    <div class="text-xs text-slate-500 mt-1">
                                        {{ $activity->device_os ?? 'Unknown OS' }}
                                    </div>

                                </td>


                                <td class="px-6 py-4">

                                    <div class="text-sm text-slate-700">
                                        {{ $activity->ip_address ?? 'Unknown' }}
                                    </div>

                                    <div class="text-xs text-slate-400 mt-1">
                                        {{ $activity->device_type ?? 'unknown' }}
                                    </div>

                                </td>


                                <td class="px-6 py-4">

                                    @if($activity->status === 'success')

                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                            Success
                                        </span>

                                    @elseif($activity->status === 'failed')

                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">
                                            Failed
                                        </span>

                                    @else

                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700">
                                            {{ ucfirst($activity->status) }}
                                        </span>

                                    @endif

                                </td>


                                <td class="px-6 py-4">

                                    <div class="text-sm font-medium text-slate-700">
                                        {{ $activity->created_at->format('M d, Y') }}
                                    </div>

                                    <div class="text-xs text-slate-400 mt-1">
                                        {{ $activity->created_at->format('h:i A') }}
                                    </div>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>


            <!-- Pagination -->

            <div class="p-6 border-t border-slate-200">
                {{ $activities->links() }}
            </div>

        @else

            <div class="p-12 text-center">

                <div class="text-5xl mb-4">
                    🔐
                </div>

                <h3 class="text-lg font-bold text-slate-800">
                    No security activity found
                </h3>

                <p class="text-sm text-slate-500 mt-2">
                    Authentication and device events will appear here.
                </p>

            </div>

        @endif

    </div>

</div>

@endsection