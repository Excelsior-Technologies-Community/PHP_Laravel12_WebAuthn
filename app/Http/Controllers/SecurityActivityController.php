<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SecurityActivityController extends Controller
{
    /**
     * Security activity dashboard.
     */
    public function dashboard(Request $request)
    {
        $user = Auth::user();

        $baseQuery = $user->securityActivities();

        /*
        |--------------------------------------------------------------------------
        | Activity List
        |--------------------------------------------------------------------------
        */

        $query = $user->securityActivities();

        /*
        |--------------------------------------------------------------------------
        | Keyword Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where(
                    'description',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'activity_type',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'ip_address',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'device_name',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'device_os',
                    'like',
                    "%{$search}%"
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Activity Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('activity')) {
            $query->where(
                'activity_type',
                $request->activity
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Status Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->status
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Exact Date
        |--------------------------------------------------------------------------
        */

        if ($request->filled('date')) {
            $query->whereDate(
                'created_at',
                $request->date
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Date Range
        |--------------------------------------------------------------------------
        */

        if ($request->filled('date_from')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->date_from
            );
        }

        if ($request->filled('date_to')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->date_to
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Quick Date Filters
        |--------------------------------------------------------------------------
        */

        if ($request->range === 'today') {
            $query->whereDate(
                'created_at',
                today()
            );
        }

        if ($request->range === '7days') {
            $query->where(
                'created_at',
                '>=',
                now()->subDays(7)
            );
        }

        if ($request->range === '30days') {
            $query->where(
                'created_at',
                '>=',
                now()->subDays(30)
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        switch ($request->get('sort')) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;

            case 'activity':
                $query->orderBy(
                    'activity_type',
                    'asc'
                );
                break;

            case 'status':
                $query->orderBy(
                    'status',
                    'asc'
                );
                break;

            default:
                $query->latest();
                break;
        }

        $activities = $query
            ->paginate(5)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $totalActivities = $baseQuery->count();

        $successfulLogins = $baseQuery
            ->whereIn('activity_type', [
                'login_success',
                'password_login',
            ])
            ->where('status', 'success')
            ->count();

        $failedAttempts = $baseQuery
            ->where('status', 'failed')
            ->count();

        $registeredDevices = $user
            ->webauthnKeys()
            ->whereNull('deleted_at')
            ->count();

        $todayActivities = $baseQuery
            ->whereDate(
                'created_at',
                today()
            )
            ->count();

        $todayLogins = $baseQuery
            ->whereIn('activity_type', [
                'login_success',
                'password_login',
            ])
            ->where('status', 'success')
            ->whereDate(
                'created_at',
                today()
            )
            ->count();

        $recentFailures = $baseQuery
            ->where('status', 'failed')
            ->where(
                'created_at',
                '>=',
                now()->subDays(7)
            )
            ->count();

        $last30Days = $baseQuery
            ->where(
                'created_at',
                '>=',
                now()->subDays(30)
            )
            ->count();

        $activityTypes = $baseQuery
            ->select('activity_type')
            ->distinct()
            ->orderBy('activity_type')
            ->pluck('activity_type');

        return view('security.dashboard', compact(
            'activities',
            'totalActivities',
            'successfulLogins',
            'failedAttempts',
            'registeredDevices',
            'todayActivities',
            'todayLogins',
            'recentFailures',
            'last30Days',
            'activityTypes'
        ));
    }

    /**
     * Export security activities as CSV.
     */
    public function export(Request $request)
    {
        $user = Auth::user();

        $query = $user->securityActivities();

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where(
                    'description',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'activity_type',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'ip_address',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'device_name',
                    'like',
                    "%{$search}%"
                );
            });
        }

        if ($request->filled('activity')) {
            $query->where(
                'activity_type',
                $request->activity
            );
        }

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->status
            );
        }

        if ($request->filled('date')) {
            $query->whereDate(
                'created_at',
                $request->date
            );
        }

        if ($request->filled('date_from')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->date_from
            );
        }

        if ($request->filled('date_to')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->date_to
            );
        }

        if ($request->range === 'today') {
            $query->whereDate(
                'created_at',
                today()
            );
        }

        if ($request->range === '7days') {
            $query->where(
                'created_at',
                '>=',
                now()->subDays(7)
            );
        }

        if ($request->range === '30days') {
            $query->where(
                'created_at',
                '>=',
                now()->subDays(30)
            );
        }

        $activities = $query
            ->latest()
            ->get();

        $filename = 'security-activity-' .
            now()->format('Y-m-d-H-i-s') .
            '.csv';

        return response()->streamDownload(
            function () use ($activities) {

                $handle = fopen(
                    'php://output',
                    'w'
                );

                fputcsv($handle, [
                    'ID',
                    'Activity',
                    'Description',
                    'Status',
                    'IP Address',
                    'Device Name',
                    'Device Type',
                    'Operating System',
                    'Created At',
                ]);

                foreach ($activities as $activity) {
                    fputcsv($handle, [
                        $activity->id,
                        $activity->activity_type,
                        $activity->description,
                        $activity->status,
                        $activity->ip_address,
                        $activity->device_name,
                        $activity->device_type,
                        $activity->device_os,
                        optional($activity->created_at)
                            ->format('Y-m-d H:i:s'),
                    ]);
                }

                fclose($handle);
            },
            $filename,
            [
                'Content-Type' => 'text/csv',
            ]
        );
    }
}