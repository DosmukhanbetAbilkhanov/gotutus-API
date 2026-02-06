<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityTypeTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'activity_type_id',
        'language_code',
        'name',
    ];

    public function activityType(): BelongsTo
    {
        return $this->belongsTo(ActivityType::class);
    }
}
