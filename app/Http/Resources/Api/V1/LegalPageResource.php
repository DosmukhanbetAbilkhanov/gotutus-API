<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\LegalPage
 */
class LegalPageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $translation = $this->getTranslation();

        return [
            'slug' => $this->slug,
            'version' => $this->version,
            'published_at' => $this->published_at?->toIso8601String(),
            'title' => $translation?->title ?? '',
            'content' => $translation?->content ?? '',
        ];
    }
}
