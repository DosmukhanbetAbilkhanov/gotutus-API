<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\JoinRequestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JoinRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'hangout_request_id',
        'user_id',
        'place_id',
        'status',
        'message',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'confirmed_at' => 'datetime',
            'status' => JoinRequestStatus::class,
        ];
    }

    public function hangoutRequest(): BelongsTo
    {
        return $this->belongsTo(HangoutRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    public function conversation(): HasOne
    {
        return $this->hasOne(Conversation::class);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', JoinRequestStatus::Pending);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', JoinRequestStatus::Approved);
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', JoinRequestStatus::Confirmed);
    }

    public function isPending(): bool
    {
        return $this->status === JoinRequestStatus::Pending;
    }

    public function isApproved(): bool
    {
        return $this->status === JoinRequestStatus::Approved;
    }

    public function isConfirmed(): bool
    {
        return $this->status === JoinRequestStatus::Confirmed;
    }
}
