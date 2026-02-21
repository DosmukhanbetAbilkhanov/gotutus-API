<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'hangout_request_id',
        'join_request_id',
    ];

    public function hangoutRequest(): BelongsTo
    {
        return $this->belongsTo(HangoutRequest::class);
    }

    public function joinRequest(): BelongsTo
    {
        return $this->belongsTo(JoinRequest::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany('created_at');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_user')
            ->withPivot('last_read_at');
    }

    /**
     * Get unread message count for a specific user.
     */
    public function unreadCountFor(int $userId): int
    {
        $pivot = $this->participants->firstWhere('id', $userId)?->pivot;
        $lastReadAt = $pivot?->last_read_at;

        $query = $this->messages()->where('user_id', '!=', $userId);

        if ($lastReadAt) {
            $query->where('created_at', '>', $lastReadAt);
        }

        return $query->count();
    }

    /**
     * Mark conversation as read for a user (upsert pivot row).
     */
    public function markAsReadFor(int $userId): void
    {
        $this->participants()->syncWithoutDetaching([
            $userId => ['last_read_at' => now()],
        ]);
    }

    /**
     * Get the other participant for a given user.
     */
    public function otherUserFor(User $user): ?User
    {
        $hangoutRequest = $this->hangoutRequest;

        if ($hangoutRequest->user_id === $user->id) {
            // Current user is the hangout owner → return the joiner
            return $this->joinRequest?->user;
        }

        // Current user is the joiner → return the hangout owner
        return $hangoutRequest->user;
    }
}
