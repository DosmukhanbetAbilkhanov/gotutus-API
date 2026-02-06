<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasTranslations
{
    /**
     * Get the translation for the current locale.
     */
    public function getTranslation(?string $locale = null): ?object
    {
        $locale = $locale ?? app()->getLocale();

        return $this->translations->firstWhere('language_code', $locale)
            ?? $this->translations->firstWhere('language_code', 'ru')
            ?? $this->translations->first();
    }

    /**
     * Get translated attribute value.
     */
    protected function getTranslatedAttribute(string $attribute): ?string
    {
        $translation = $this->getTranslation();

        return $translation?->{$attribute};
    }

    /**
     * Get the name attribute (translated).
     */
    public function getNameAttribute(): ?string
    {
        return $this->getTranslatedAttribute('name');
    }

    /**
     * Scope to eager load translations.
     */
    public function scopeWithTranslations($query): void
    {
        $query->with('translations');
    }
}
