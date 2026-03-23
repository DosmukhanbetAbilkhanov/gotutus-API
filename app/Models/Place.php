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
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Place extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $fillable = [
        'city_id',
        'logo_path',
        'phone',
        'website',
        'instagram',
        'two_gis_url',
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

    public function workingHours(): HasMany
    {
        return $this->hasMany(PlaceWorkingHour::class)->orderBy('day_of_week');
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(PlaceDiscount::class);
    }

    public function activeDiscount(): HasOne
    {
        return $this->hasOne(PlaceDiscount::class)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->latest();
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

    public function photos(): HasMany
    {
        return $this->hasMany(PlacePhoto::class)->orderBy('sort_order');
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo_path) {
            return null;
        }

        return Storage::disk('public')->url($this->logo_path);
    }

    public function getAddressAttribute(): ?string
    {
        return $this->getTranslatedAttribute('address');
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->getTranslatedAttribute('description');
    }
}
