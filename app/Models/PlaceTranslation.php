<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaceTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'place_id',
        'language_code',
        'name',
        'address',
    ];

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }
}
