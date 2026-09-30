<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WebauthnKey extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'webauthn_credentials';

    protected $fillable = [
        'authenticatable_type',
        'authenticatable_id',
        'user_id',
        'name',
        'alias',
        'credential_id',
        'credential_public_key',
        'transports',
        'sign_count',
        'rp_id',
        'origin',
        'last_used_at',
        'device_type',
        'device_os',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'last_used_at' => 'datetime',
        'transports' => 'array',
        'sign_count' => 'integer',
    ];

    protected $appends = [
        'security_score',
    ];

    public function authenticatable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function securityActivities()
    {
        return $this->hasMany(
            SecurityActivity::class,
            'device_id',
            'id'
        );
    }

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where(
            'user_id',
            $userId
        );
    }

    public function scopeRecentlyAdded(
        $query,
        $days = 7
    ) {
        return $query->where(
            'created_at',
            '>=',
            now()->subDays($days)
        );
    }

    public function scopeRecentlyUsed(
        $query,
        $days = 30
    ) {
        return $query
            ->whereNotNull('last_used_at')
            ->where(
                'last_used_at',
                '>=',
                now()->subDays($days)
            );
    }

    public function scopeNeverUsed($query)
    {
        return $query->whereNull(
            'last_used_at'
        );
    }

    public function scopeInactive(
        $query,
        $days = 30
    ) {
        return $query->where(function ($q) use ($days) {
            $q->whereNull('last_used_at')
                ->orWhere(
                    'last_used_at',
                    '<',
                    now()->subDays($days)
                );
        });
    }

    public function getTransportsList()
    {
        if (empty($this->transports)) {
            return 'Unknown';
        }

        $transports = is_array($this->transports)
            ? $this->transports
            : json_decode(
                $this->transports,
                true
            ) ?? [];

        if (empty($transports)) {
            return 'Unknown';
        }

        $labels = [
            'internal' => '📱 Internal',
            'external' => '🔑 External',
            'ble' => '📡 Bluetooth',
            'nfc' => '📳 NFC',
            'usb' => '🔌 USB',
            'platform' => '💻 Platform',
            'hybrid' => '🔄 Hybrid',
        ];

        return implode(
            ', ',
            array_map(
                function ($transport) use ($labels) {
                    return $labels[$transport]
                        ?? ucfirst(
                            str_replace(
                                ['_', '-'],
                                ' ',
                                $transport
                            )
                        );
                },
                $transports
            )
        );
    }

    public function getDeviceTypeLabel()
    {
        $types = [
            'security_key' =>
                '🔐 Security Key',

            'platform' =>
                '💻 Platform Authenticator',

            'phone' =>
                '📱 Phone',

            'tablet' =>
                '📱 Tablet',

            'laptop' =>
                '💻 Laptop',

            'desktop' =>
                '🖥️ Desktop',

            'computer' =>
                '💻 Computer',

            'mobile' =>
                '📱 Mobile',

            'unknown' =>
                '❓ Unknown Device',
        ];

        return $types[$this->device_type]
            ?? $types['unknown'];
    }

    public function markAsUsed(
        ?int $signCount = null
    ) {
        $data = [
            'last_used_at' => now(),
        ];

        if ($signCount !== null) {
            $data['sign_count'] = $signCount;
        }

        $this->update($data);

        return $this;
    }

    public function getFormattedCreatedAt()
    {
        return $this->created_at
            ? $this->created_at
                ->format('M d, Y \a\t h:i A')
            : 'Unknown';
    }

    public function getCreatedAtDiffForHumans()
    {
        return $this->created_at
            ? $this->created_at->diffForHumans()
            : 'Unknown';
    }

    public function getDaysSinceLastUse()
    {
        if (!$this->last_used_at) {
            return null;
        }

        return $this->last_used_at
            ->diffInDays(now());
    }

    public function hasBeenUsed()
    {
        return !is_null(
            $this->last_used_at
        );
    }

    public function isPossiblyCloned(
        int $newSignCount
    ) {
        if ($this->sign_count === 0) {
            return false;
        }

        return $newSignCount <=
            $this->sign_count;
    }

    public function getSecurityScore()
    {
        $score = 100;

        if (
            $this->last_used_at &&
            $this->last_used_at
                ->diffInDays(now()) > 30
        ) {
            $score -= 10;
        }

        if (
            $this->created_at &&
            $this->created_at
                ->diffInMonths(now()) > 3 &&
            !$this->last_used_at
        ) {
            $score -= 15;
        }

        if (
            $this->device_type ===
            'security_key'
        ) {
            $score += 10;
        }

        if (
            is_array($this->transports) &&
            count($this->transports) > 1
        ) {
            $score += 5;
        }

        return min($score, 100);
    }

    public function getSecurityScoreAttribute()
    {
        return $this->getSecurityScore();
    }

    public function archive()
    {
        return $this->delete();
    }

    public function permanentlyDelete()
    {
        return $this->forceDelete();
    }

    public function restore()
    {
        return parent::restore();
    }

    public function toApiArray()
    {
        return [
            'id' => $this->id,

            'name' =>
                $this->alias
                ?: $this->name,

            'type' =>
                $this->getDeviceTypeLabel(),

            'transports' =>
                $this->getTransportsList(),

            'created_at' =>
                $this->getFormattedCreatedAt(),

            'last_used_at' =>
                $this->last_used_at
                ? $this->last_used_at
                    ->format('M d, Y')
                : 'Never',

            'security_score' =>
                $this->getSecurityScore(),

            'sign_count' =>
                $this->sign_count,

            'device_os' =>
                $this->device_os,

            'rp_id' =>
                $this->rp_id,
        ];
    }
}