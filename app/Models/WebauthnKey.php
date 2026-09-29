<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WebauthnKey extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'webauthn_credentials';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'last_used_at' => 'datetime',
        'transports' => 'array',
        'sign_count' => 'integer',
    ];

    /**
     * Get the authenticatable model.
     */
    public function authenticatable()
    {
        return $this->morphTo();
    }

    /**
     * Get the user that owns this credential.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get security activities related to this credential.
     */
    public function securityActivities()
    {
        return $this->hasMany(
            SecurityActivity::class,
            'device_id'
        );
    }

    /**
     * Scope: Get only active credentials.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Scope: Get credentials for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Get credentials created in the last N days.
     */
    public function scopeRecentlyAdded($query, $days = 7)
    {
        return $query->where(
            'created_at',
            '>=',
            now()->subDays($days)
        );
    }

    /**
     * Get human-readable transports.
     */
    public function getTransportsList()
    {
        if (empty($this->transports)) {
            return 'Unknown';
        }

        $transports = is_array($this->transports)
            ? $this->transports
            : json_decode($this->transports, true) ?? [];

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

        return implode(', ', array_map(
            function ($transport) use ($labels) {
                return $labels[$transport]
                    ?? ucfirst(str_replace(
                        ['_', '-'],
                        ' ',
                        $transport
                    ));
            },
            $transports
        ));
    }

    /**
     * Get device type label.
     */
    public function getDeviceTypeLabel()
    {
        $types = [
            'security_key' => '🔐 Security Key',
            'platform' => '💻 Platform Authenticator',
            'phone' => '📱 Phone',
            'tablet' => '📱 Tablet',
            'laptop' => '💻 Laptop',
            'desktop' => '🖥️ Desktop',
            'mobile' => '📱 Mobile',
            'unknown' => '❓ Unknown Device',
        ];

        return $types[$this->device_type] ?? $types['unknown'];
    }

    /**
     * Mark credential as successfully used.
     *
     * The WebAuthn controller should update sign_count
     * using the authenticator's actual counter.
     */
    public function markAsUsed(?int $signCount = null)
    {
        $data = [
            'last_used_at' => now(),
        ];

        if ($signCount !== null) {
            $data['sign_count'] = $signCount;
        }

        $this->update($data);

        return $this;
    }

    /**
     * Get formatted creation date.
     */
    public function getFormattedCreatedAt()
    {
        return $this->created_at
            ? $this->created_at->format('M d, Y \a\t h:i A')
            : 'Unknown';
    }

    /**
     * Get readable creation time.
     */
    public function getCreatedAtDiffForHumans()
    {
        return $this->created_at
            ? $this->created_at->diffForHumans()
            : 'Unknown';
    }

    /**
     * Get days since last use.
     */
    public function getDaysSinceLastUse()
    {
        if (!$this->last_used_at) {
            return null;
        }

        return $this->last_used_at->diffInDays(now());
    }

    /**
     * Check if credential has been used.
     */
    public function hasBeenUsed()
    {
        return !is_null($this->last_used_at);
    }

    /**
     * Check whether the supplied authenticator counter
     * indicates a possible cloned credential.
     *
     * A counter of 0 can legitimately remain 0.
     */
    public function isPossiblyCloned(int $newSignCount)
    {
        if ($this->sign_count === 0) {
            return false;
        }

        return $newSignCount <= $this->sign_count;
    }

    /**
     * Get credential security score.
     */
    public function getSecurityScore()
    {
        $score = 100;

        // Deduct if not used recently.
        if (
            $this->last_used_at &&
            $this->last_used_at->diffInDays(now()) > 30
        ) {
            $score -= 10;
        }

        // Deduct if old and never used.
        if (
            $this->created_at &&
            $this->created_at->diffInMonths(now()) > 3 &&
            !$this->last_used_at
        ) {
            $score -= 15;
        }

        // Bonus for security key.
        if ($this->device_type === 'security_key') {
            $score += 10;
        }

        // Bonus for multiple transports.
        if (
            is_array($this->transports) &&
            count($this->transports) > 1
        ) {
            $score += 5;
        }

        return min($score, 100);
    }

    /**
     * Archive a credential using soft delete.
     */
    public function archive()
    {
        return $this->delete();
    }

    /**
     * Permanently delete a credential.
     */
    public function permanentlyDelete()
    {
        return $this->forceDelete();
    }

    /**
     * Restore an archived credential.
     */
    public function restore()
    {
        return parent::restore();
    }

    /**
     * Get credential details for API.
     */
    public function toApiArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->alias ?: $this->name,
            'type' => $this->getDeviceTypeLabel(),
            'transports' => $this->getTransportsList(),
            'created_at' => $this->getFormattedCreatedAt(),
            'last_used_at' => $this->last_used_at
                ? $this->last_used_at->format('M d, Y')
                : 'Never',
            'security_score' => $this->getSecurityScore(),
            'sign_count' => $this->sign_count,
            'device_os' => $this->device_os,
            'rp_id' => $this->rp_id,
        ];
    }
}