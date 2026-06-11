<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PhotoStatus;
use App\Enums\VerificationPose;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhotoVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'selfie_path',
        'pose',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PhotoStatus::class,
            'pose' => VerificationPose::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', PhotoStatus::Pending);
    }
}
