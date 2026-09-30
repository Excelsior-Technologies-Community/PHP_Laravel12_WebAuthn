<?php

namespace App\Http\Controllers;

use App\Models\WebauthnKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SecurityDeviceController extends Controller
{
    /**
     * Display security devices with search, filters and sorting.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = $user->webauthnKeys()
            ->whereNull('deleted_at');

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('alias', 'like', "%{$search}%")
                    ->orWhere('device_type', 'like', "%{$search}%")
                    ->orWhere('device_os', 'like', "%{$search}%")
                    ->orWhere('rp_id', 'like', "%{$search}%");
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Device Type Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('device_type')) {
            $query->where(
                'device_type',
                $request->device_type
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Operating System Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('device_os')) {
            $query->where(
                'device_os',
                $request->device_os
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Usage Filter
        |--------------------------------------------------------------------------
        */

        if ($request->usage === 'recent') {
            $query->whereNotNull('last_used_at')
                ->where(
                    'last_used_at',
                    '>=',
                    now()->subDays(30)
                );
        }

        if ($request->usage === 'never') {
            $query->whereNull('last_used_at');
        }

        if ($request->usage === 'inactive') {
            $query->where(function ($q) {
                $q->whereNull('last_used_at')
                    ->orWhere(
                        'last_used_at',
                        '<',
                        now()->subDays(30)
                    );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Security Status Filter
        |--------------------------------------------------------------------------
        */

        if ($request->security === 'high') {
            $query->where(function ($q) {
                $q->where('device_type', 'security_key')
                    ->orWhereNotNull('last_used_at');
            });
        }

        if ($request->security === 'unused') {
            $query->whereNull('last_used_at');
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        $sort = $request->get('sort', 'newest');

        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;

            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;

            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;

            case 'last_used':
                $query->orderByDesc('last_used_at');
                break;

            case 'least_used':
                $query->orderBy('last_used_at', 'asc');
                break;

            case 'score':
                /*
                 * Security score is an accessor, so database sorting
                 * is not possible directly. We sort after retrieval.
                 */
                $devices = $query
                    ->get()
                    ->sortByDesc(function ($device) {
                        return $device->security_score;
                    })
                    ->values();

                $devices = $this->paginateCollection(
                    $devices,
                    5,
                    $request
                );

                return view('security.devices', [
                    'devices' => $devices,
                    'deviceTypes' => $this->deviceTypes($user),
                    'deviceOs' => $this->deviceOperatingSystems($user),
                    'totalDevices' => $this->totalDevices($user),
                    'recentDevices' => $this->recentDevices($user),
                    'neverUsedDevices' => $this->neverUsedDevices($user),
                    'inactiveDevices' => $this->inactiveDevices($user),
                ]);

            default:
                $query->orderByDesc('created_at');
                break;
        }

        $devices = $query
            ->paginate(5)
            ->withQueryString();

        return view('security.devices', [
            'devices' => $devices,
            'deviceTypes' => $this->deviceTypes($user),
            'deviceOs' => $this->deviceOperatingSystems($user),
            'totalDevices' => $this->totalDevices($user),
            'recentDevices' => $this->recentDevices($user),
            'neverUsedDevices' => $this->neverUsedDevices($user),
            'inactiveDevices' => $this->inactiveDevices($user),
        ]);
    }

    /**
     * Export filtered devices as CSV.
     */
    public function export(Request $request)
    {
        $user = Auth::user();

        $query = $user->webauthnKeys()
            ->whereNull('deleted_at');

        /*
        |--------------------------------------------------------------------------
        | Same filters as device listing
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('alias', 'like', "%{$search}%")
                    ->orWhere('device_type', 'like', "%{$search}%")
                    ->orWhere('device_os', 'like', "%{$search}%")
                    ->orWhere('rp_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('device_type')) {
            $query->where(
                'device_type',
                $request->device_type
            );
        }

        if ($request->filled('device_os')) {
            $query->where(
                'device_os',
                $request->device_os
            );
        }

        if ($request->usage === 'recent') {
            $query->whereNotNull('last_used_at')
                ->where(
                    'last_used_at',
                    '>=',
                    now()->subDays(30)
                );
        }

        if ($request->usage === 'never') {
            $query->whereNull('last_used_at');
        }

        if ($request->usage === 'inactive') {
            $query->where(function ($q) {
                $q->whereNull('last_used_at')
                    ->orWhere(
                        'last_used_at',
                        '<',
                        now()->subDays(30)
                    );
            });
        }

        $devices = $query
            ->orderByDesc('created_at')
            ->get();

        $filename = 'webauthn-devices-' .
            now()->format('Y-m-d-H-i-s') .
            '.csv';

        return response()->streamDownload(
            function () use ($devices) {

                $handle = fopen('php://output', 'w');

                fputcsv($handle, [
                    'ID',
                    'Device Name',
                    'Alias',
                    'Device Type',
                    'Operating System',
                    'RP ID',
                    'Created At',
                    'Last Used',
                    'Sign Count',
                    'Security Score',
                    'Transports',
                ]);

                foreach ($devices as $device) {
                    fputcsv($handle, [
                        $device->id,
                        $device->name,
                        $device->alias,
                        $device->device_type,
                        $device->device_os,
                        $device->rp_id,
                        optional($device->created_at)
                            ->format('Y-m-d H:i:s'),
                        optional($device->last_used_at)
                            ->format('Y-m-d H:i:s') ?? 'Never',
                        $device->sign_count,
                        $device->security_score,
                        $device->getTransportsList(),
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

    /**
     * Device type options.
     */
    private function deviceTypes($user)
    {
        return $user->webauthnKeys()
            ->whereNull('deleted_at')
            ->whereNotNull('device_type')
            ->where('device_type', '!=', '')
            ->select('device_type')
            ->distinct()
            ->orderBy('device_type')
            ->pluck('device_type');
    }

    /**
     * Operating system options.
     */
    private function deviceOperatingSystems($user)
    {
        return $user->webauthnKeys()
            ->whereNull('deleted_at')
            ->whereNotNull('device_os')
            ->where('device_os', '!=', '')
            ->select('device_os')
            ->distinct()
            ->orderBy('device_os')
            ->pluck('device_os');
    }

    private function totalDevices($user)
    {
        return $user->webauthnKeys()
            ->whereNull('deleted_at')
            ->count();
    }

    private function recentDevices($user)
    {
        return $user->webauthnKeys()
            ->whereNull('deleted_at')
            ->whereNotNull('last_used_at')
            ->where(
                'last_used_at',
                '>=',
                now()->subDays(30)
            )
            ->count();
    }

    private function neverUsedDevices($user)
    {
        return $user->webauthnKeys()
            ->whereNull('deleted_at')
            ->whereNull('last_used_at')
            ->count();
    }

    private function inactiveDevices($user)
    {
        return $user->webauthnKeys()
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->whereNull('last_used_at')
                    ->orWhere(
                        'last_used_at',
                        '<',
                        now()->subDays(30)
                    );
            })
            ->count();
    }

    /**
     * Manual pagination for security-score sorting.
     */
    private function paginateCollection(
        $items,
        int $perPage,
        Request $request
    ) {
        $page = max(
            1,
            (int) $request->get('page', 1)
        );

        $total = $items->count();

        $results = $items
            ->slice(
                ($page - 1) * $perPage,
                $perPage
            )
            ->values();

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $results,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }
}