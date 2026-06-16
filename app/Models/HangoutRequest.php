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
        'goal_id',
        'place_id',
        'place_advertisement_id',
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

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    public function placeAdvertisement(): BelongsTo
    {
        return $this->belongsTo(PlaceAdvertisement::class);
    }

    public function joinRequests(): HasMany
    {
        return $this->hasMany(JoinRequest::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * The single group conversation for this hangout (join_request_id IS NULL).
     */
    public function groupConversation(): HasOne
    {
        return $this->hasOne(Conversation::class)->whereNull('join_request_id');
    }

    /**
     * Ensure the per-hangout group conversation exists and its membership
     * (host + all confirmed joiners) is in sync.
     *
     * A group room is only created once there are at least 2 confirmed joiners
     * (so the room has host + 2 = 3 members) to avoid duplicating the 1:1
     * join-request chat. If members drop below the threshold the existing room
     * is kept (history preserved) but membership is still synced.
     */
    public function syncGroupConversation(): ?Conversation
    {
        $confirmedUserIds = $this->joinRequests()
            ->where('status', JoinRequestStatus::Confirmed)
            ->pluck('user_id')
            ->all();

        $conversation = $this->groupConversation()->first();

        $memberIds = array_values(array_unique([$this->user_id, ...$confirmedUserIds]));

        if (count($confirmedUserIds) < 2) {
            // Below threshold: don't create a room, but keep an existing one in sync.
            $conversation?->participants()->sync($memberIds);

            return $conversation;
        }

        if (! $conversation) {
            $conversation = Conversation::create([
                'hangout_request_id' => $this->id,
                'join_request_id' => null,
            ]);
        }

        // sync() attaches new members, detaches removed ones, and leaves existing
        // pivot rows (last_read_at) untouched.
        $conversation->participants()->sync($memberIds);

        return $conversation;
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

    public function scopeFromAd(Builder $query): Builder
    {
        return $query->whereNotNull('place_advertisement_id');
    }

    public function scopeOrganic(Builder $query): Builder
    {
        return $query->whereNull('place_advertisement_id');
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
