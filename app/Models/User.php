<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get all WebAuthn credentials belonging to this user.
     */
    public function webauthnKeys()
    {
        return $this->hasMany(
            WebauthnKey::class,
            'user_id'
        );
    }

    /**
     * Get only active WebAuthn credentials.
     */
    public function activeWebauthnKeys()
    {
        return $this->webauthnKeys()
            ->whereNull('deleted_at');
    }

    /**
     * Get all security activities belonging to this user.
     */
    public function securityActivities()
    {
        return $this->hasMany(
            SecurityActivity::class,
            'user_id'
        );
    }

    /**
     * Get recent security activities.
     */
    public function recentSecurityActivities($limit = 10)
    {
        return $this->securityActivities()
            ->latest()
            ->limit($limit);
    }

    /**
     * Get successful security activities.
     */
    public function successfulSecurityActivities()
    {
        return $this->securityActivities()
            ->where('status', 'success');
    }

    /**
     * Get failed security activities.
     */
    public function failedSecurityActivities()
    {
        return $this->securityActivities()
            ->where('status', 'failed');
    }

    /**
     * Add a new WebAuthn credential.
     *
     * The transports array is automatically converted to JSON
     * by the WebauthnKey model cast.
     */
    public function addWebauthnKey(
        string $name,
        string $credentialPublicKey,
        string $credentialId,
        array $transports = []
    ) {
        return $this->webauthnKeys()->create([
            'name' => $name,
            'credential_id' => $credentialId,
            'credential_public_key' => $credentialPublicKey,
            'transports' => $transports,
            'sign_count' => 0,
        ]);
    }

    /**
     * Get the number of active WebAuthn devices.
     */
    public function activeWebauthnDeviceCount()
    {
        return $this->webauthnKeys()
            ->whereNull('deleted_at')
            ->count();
    }

    /**
     * Get the number of WebAuthn devices that have been used.
     */
    public function usedWebauthnDeviceCount()
    {
        return $this->webauthnKeys()
            ->whereNull('deleted_at')
            ->whereNotNull('last_used_at')
            ->count();
    }

    /**
     * Get the number of security activities today.
     */
    public function todaySecurityActivityCount()
    {
        return $this->securityActivities()
            ->whereDate('created_at', today())
            ->count();
    }
}