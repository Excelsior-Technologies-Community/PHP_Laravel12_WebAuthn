<?php

namespace App\Http\Controllers;

use App\Models\SecurityActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SecurityActivityController extends Controller
{
    /**
     * Security dashboard.
     */
    public function dashboard(Request $request)
    {
        $user = Auth::user();

        $query = $user->securityActivities()->latest();

        // Activity filter
        if ($request->filled('activity')) {
            $query->where('activity_type', $request->activity);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date filter
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $activities = $query->paginate(10)->withQueryString();

        // Statistics
        $totalActivities = $user->securityActivities()->count();

        $successfulLogins = $user->securityActivities()
            ->whereIn('activity_type', [
                'login_success',
                'password_login',
            ])
            ->where('status', 'success')
            ->count();

        $failedAttempts = $user->securityActivities()
            ->where('status', 'failed')
            ->count();

        $registeredDevices = $user->webauthnKeys()
            ->whereNull('deleted_at')
            ->count();

        $todayActivities = $user->securityActivities()
            ->whereDate('created_at', today())
            ->count();

        $todayLogins = $user->securityActivities()
            ->whereIn('activity_type', [
                'login_success',
                'password_login',
            ])
            ->where('status', 'success')
            ->whereDate('created_at', today())
            ->count();

        $recentFailures = $user->securityActivities()
            ->where('status', 'failed')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $activityTypes = $user->securityActivities()
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
            'activityTypes'
        ));
    }
}