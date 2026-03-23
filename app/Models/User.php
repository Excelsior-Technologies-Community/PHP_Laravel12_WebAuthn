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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get user's WebAuthn keys (Relationship)
     */
    public function webauthnKeys()
    {
        return $this->hasMany(WebauthnKey::class, 'user_id');
    }

    /**
     * Add a new WebAuthn key for this user
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
            'transports' => json_encode($transports),
            'sign_count' => 0,
        ]);
    }
}