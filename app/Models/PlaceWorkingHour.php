<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaceWorkingHour extends Model
{
    protected $fillable = [
        'place_id',
        'day_of_week',
        'open_time',
        'close_time',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
    ];

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }
}
