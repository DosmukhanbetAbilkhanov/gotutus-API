<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Gender;
use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens;

    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'age',
        'gender',
        'bio',
        'password',
        'city_id',
        'status',
        'user_type_id',
        'phone_verified_at',
        'is_online',
        'last_seen_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'age' => 'integer',
            'gender' => Gender::class,
            'status' => UserStatus::class,
            'is_online' => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }

    public function deviceTokens(): HasMany
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function routeNotificationForFcm(): array
    {
        return $this->deviceTokens()->pluck('token')->toArray();
    }

    public function userType(): BelongsTo
    {
        return $this->belongsTo(UserType::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(UserPhoto::class);
    }

    public function hangoutRequests(): HasMany
    {
        return $this->hasMany(HangoutRequest::class);
    }

    public function joinRequests(): HasMany
    {
        return $this->hasMany(JoinRequest::class);
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function blockedUsers(): HasMany
    {
        return $this->hasMany(BlockedUser::class);
    }

    public function blockedByUsers(): HasMany
    {
        return $this->hasMany(BlockedUser::class, 'blocked_user_id');
    }

    public function reportsSent(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function reportsReceived(): HasMany
    {
        return $this->hasMany(Report::class, 'reported_user_id');
    }

    public function refreshTokens(): HasMany
    {
        return $this->hasMany(RefreshToken::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', UserStatus::Active);
    }

    public function scopeInCity(Builder $query, int $cityId): Builder
    {
        return $query->where('city_id', $cityId);
    }

    public function scopeNotBlockedBy(Builder $query, int $userId): Builder
    {
        return $query->whereDoesntHave('blockedByUsers', function (Builder $q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    public function isAdmin(): bool
    {
        return $this->userType?->slug === UserType::SLUG_ADMIN;
    }

    public function isCityManager(): bool
    {
        return $this->userType?->slug === UserType::SLUG_CITY_MANAGER;
    }

    public function isClient(): bool
    {
        return $this->userType?->slug === UserType::SLUG_CLIENT;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin' => $this->isAdmin(),
            'city-manager' => $this->isCityManager(),
            default => false,
        };
    }

    public function isPhoneVerified(): bool
    {
        return $this->phone_verified_at !== null;
    }
}
