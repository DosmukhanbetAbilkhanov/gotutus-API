<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterestTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'interest_id',
        'language_code',
        'name',
    ];

    public function interest(): BelongsTo
    {
        return $this->belongsTo(Interest::class);
    }
}
