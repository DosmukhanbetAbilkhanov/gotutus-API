<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LegalPage extends Model
{
    use HasTranslations;

    public const SLUG_PUBLIC_OFFER = 'public-offer';

    protected $fillable = [
        'slug',
        'version',
        'is_active',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(LegalPageTranslation::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeBySlug(Builder $query, string $slug): Builder
    {
        return $query->where('slug', $slug);
    }

    public static function getActive(string $slug): ?self
    {
        return static::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with('translations')
            ->first();
    }

    /**
     * When activating a page, deactivate other active pages with the same slug.
     */
    protected static function booted(): void
    {
        static::saving(function (LegalPage $page) {
            if ($page->is_active && $page->isDirty('is_active')) {
                static::query()
                    ->where('slug', $page->slug)
                    ->where('id', '!=', $page->id ?? 0)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }
        });
    }
}
