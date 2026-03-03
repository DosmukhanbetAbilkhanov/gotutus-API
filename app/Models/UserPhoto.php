<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PhotoStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'photo_url',
        'status',
        'rejection_reason',
        'is_approved',
    ];

    protected function casts(): array
    {
        return [
            'status' => PhotoStatus::class,
            'is_approved' => 'boolean',
        ];
    }

    /**
     * Derive is_approved from the status field.
     */
    public function getIsApprovedAttribute(): bool
    {
        return $this->status === PhotoStatus::Approved;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', PhotoStatus::Approved);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', PhotoStatus::Pending);
    }
}
