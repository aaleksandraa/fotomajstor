<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Observers\UserObserver;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[ObservedBy([UserObserver::class])]
class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'role',
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
            'role' => UserRole::class,
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin' => $this->role === UserRole::Admin,
            'dashboard' => $this->role === UserRole::Photographer,
            default => false,
        };
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function photographerProfile(): HasOne
    {
        return $this->hasOne(PhotographerProfile::class);
    }

    public function publishVerifiedPhotographerProfile(): void
    {
        if ($this->role !== UserRole::Photographer || ! $this->hasVerifiedEmail()) {
            return;
        }

        $this->photographerProfile()->update([
            'active' => true,
            'verified' => true,
        ]);
    }
}
