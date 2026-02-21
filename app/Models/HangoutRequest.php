<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\HangoutRequestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HangoutRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'city_id',
        'activity_type_id',
        'place_id',
        'date',
        'time',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'time' => 'datetime:H:i',
            'status' => HangoutRequestStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function activityType(): BelongsTo
    {
        return $this->belongsTo(ActivityType::class);
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    public function joinRequests(): HasMany
    {
        return $this->hasMany(JoinRequest::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function confirmedJoinRequest(): HasOne
    {
        return $this->hasOne(JoinRequest::class)->confirmed();
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', HangoutRequestStatus::Open);
    }

    public function scopeInCity(Builder $query, int $cityId): Builder
    {
        return $query->where('city_id', $cityId);
    }

    public function scopeForDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('date', $date);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('date', '>=', now()->toDateString());
    }

    public function scopeForActivityType(Builder $query, int $activityTypeId): Builder
    {
        return $query->where('activity_type_id', $activityTypeId);
    }

    public function scopeExcludeBlockedUsers(Builder $query, int $userId): Builder
    {
        return $query->whereHas('user', function (Builder $q) use ($userId) {
            $q->notBlockedBy($userId);
        });
    }

    public function scopeNotOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', '!=', $userId);
    }
}
