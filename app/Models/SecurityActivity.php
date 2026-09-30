<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'activity_type',
        'description',
        'ip_address',
        'user_agent',
        'device_id',
        'device_name',
        'device_type',
        'device_os',
        'credential_id',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }

    public function device()
    {
        return $this->belongsTo(
            WebauthnKey::class,
            'device_id'
        );
    }

    public function getActivityLabelAttribute()
    {
        return match ($this->activity_type) {

            'login_success' =>
                'Successful Login',

            'login_failed' =>
                'Failed Login',

            'device_registered' =>
                'Device Registered',

            'device_deleted' =>
                'Device Removed',

            'device_renamed' =>
                'Device Renamed',

            'password_login' =>
                'Password Login',

            'logout' =>
                'Logout',

            default =>
                ucwords(
                    str_replace(
                        '_',
                        ' ',
                        $this->activity_type
                    )
                ),
        };
    }

    public function getStatusLabelAttribute()
    {
        return match ($this->status) {

            'success' =>
                'Success',

            'failed' =>
                'Failed',

            'warning' =>
                'Warning',

            default =>
                ucfirst($this->status),
        };
    }

    public function scopeForUser(
        $query,
        $userId
    ) {
        return $query->where(
            'user_id',
            $userId
        );
    }

    public function scopeRecent(
        $query,
        $days = 30
    ) {
        return $query->where(
            'created_at',
            '>=',
            now()->subDays($days)
        );
    }

    public function scopeSuccessful($query)
    {
        return $query->where(
            'status',
            'success'
        );
    }

    public function scopeFailed($query)
    {
        return $query->where(
            'status',
            'failed'
        );
    }
}