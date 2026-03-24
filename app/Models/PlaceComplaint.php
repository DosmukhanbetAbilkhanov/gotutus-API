<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlaceComplaintStatus;
use App\Enums\PlaceComplaintType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaceComplaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'hangout_request_id',
        'user_id',
        'place_id',
        'type',
        'description',
        'status',
        'admin_notes',
        'resolved_at',
        'resolved_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => PlaceComplaintType::class,
            'status' => PlaceComplaintStatus::class,
            'resolved_at' => 'datetime',
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

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
