<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Place extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $fillable = [
        'city_id',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(PlaceTranslation::class);
    }

    public function activityTypes(): BelongsToMany
    {
        return $this->belongsToMany(ActivityType::class);
    }

    public function hangoutRequests(): HasMany
    {
        return $this->hasMany(HangoutRequest::class);
    }

    public function joinRequests(): HasMany
    {
        return $this->hasMany(JoinRequest::class);
    }

    public function scopeInCity(Builder $query, int $cityId): Builder
    {
        return $query->where('city_id', $cityId);
    }

    public function scopeForActivityType(Builder $query, int $activityTypeId): Builder
    {
        return $query->whereHas('activityTypes', function (Builder $q) use ($activityTypeId) {
            $q->where('activity_types.id', $activityTypeId);
        });
    }

    public function getAddressAttribute(): ?string
    {
        return $this->getTranslatedAttribute('address');
    }
}
