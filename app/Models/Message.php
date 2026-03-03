<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Message extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'message',
        'image_url',
        'deleted_at',
        'deleted_for_everyone',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'deleted_at' => 'datetime',
            'deleted_for_everyone' => 'boolean',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deletedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'message_deletions');
    }

    public function isDeletedFor(int $userId): bool
    {
        if ($this->deleted_for_everyone) {
            return true;
        }

        return $this->deletedByUsers()->where('user_id', $userId)->exists();
    }

    /**
     * Scope to exclude messages deleted for a specific user.
     */
    public function scopeVisibleTo(Builder $query, int $userId): Builder
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('deleted_for_everyone', false)
                ->whereDoesntHave('deletedByUsers', fn ($q2) => $q2->where('user_id', $userId));
        });
    }
}
