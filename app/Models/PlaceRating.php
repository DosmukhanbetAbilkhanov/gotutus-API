<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaceRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'hangout_request_id',
        'user_id',
        'place_id',
        'rating',
        'comment',
        'discount_was_active',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'discount_was_active' => 'boolean',
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
}
