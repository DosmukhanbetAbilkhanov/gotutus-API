<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Gender;
use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
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
        'password',
        'city_id',
        'status',
        'phone_verified_at',
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
        ];
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

    public function isPhoneVerified(): bool
    {
        return $this->phone_verified_at !== null;
    }
}
