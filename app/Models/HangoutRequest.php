<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BillSplit;
use App\Enums\HangoutRequestStatus;
use App\Enums\JoinRequestStatus;
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
        'max_participants',
        'bill_split',
        'feedback_requested_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'time' => 'datetime:H:i',
            'status' => HangoutRequestStatus::class,
            'bill_split' => BillSplit::class,
            'feedback_requested_at' => 'datetime',
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

    public function ratings(): HasMany
    {
        return $this->hasMany(HangoutRating::class);
    }

    public function attendanceReports(): HasMany
    {
        return $this->hasMany(AttendanceReport::class);
    }

    public function placeRating(): HasOne
    {
        return $this->hasOne(PlaceRating::class);
    }

    public function placeComplaints(): HasMany
    {
        return $this->hasMany(PlaceComplaint::class);
    }

    public function getCompletedParticipants(): array
    {
        $participants = collect([$this->user]);
        $joiners = $this->joinRequests()
            ->whereIn('status', ['approved', 'confirmed'])
            ->with(['user.photos' => fn ($q) => $q->where('status', 'approved')])
            ->get()
            ->pluck('user');

        return \App\Http\Resources\Api\V1\UserResource::collection(
            $participants->merge($joiners)->unique('id')
        )->resolve();
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

    public function approvedCount(): int
    {
        return $this->joinRequests()
            ->whereIn('status', [JoinRequestStatus::Approved->value, JoinRequestStatus::Confirmed->value])
            ->count();
    }

    public function isFull(): bool
    {
        if ($this->max_participants === null) {
            return false;
        }

        return $this->approvedCount() >= $this->max_participants;
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->open()->where(function (Builder $q) {
            $q->whereNull('max_participants')
                ->orWhereColumn(
                    'max_participants',
                    '>',
                    \Illuminate\Support\Facades\DB::raw(
                        '(SELECT COUNT(*) FROM join_requests WHERE join_requests.hangout_request_id = hangout_requests.id AND join_requests.status IN (\'approved\', \'confirmed\'))'
                    )
                );
        });
    }
}
