<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivityType extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $fillable = [
        'slug',
        'bg_photo',
        'icon',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ActivityTypeTranslation::class);
    }

    public function places(): BelongsToMany
    {
        return $this->belongsToMany(Place::class);
    }

    public function hangoutRequests(): HasMany
    {
        return $this->hasMany(HangoutRequest::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
