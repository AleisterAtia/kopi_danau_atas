<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use NotificationChannels\WebPush\HasPushSubscriptions;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    use HasFactory, HasPushSubscriptions, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'avatar',
        'role',
        'google_id',
        'google_token',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /* ---- Relationships ---- */

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /* ---- Helpers ---- */

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Google login stores a full external URL in `avatar`; a manually
     * uploaded photo stores a local storage path instead. Normalise both
     * into a usable URL here so views don't need to know the difference.
     */
    public function avatarUrl(): ?string
    {
        if (blank($this->avatar)) {
            return null;
        }

        return str_starts_with($this->avatar, 'http')
            ? $this->avatar
            : Storage::url($this->avatar);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
    }
}
