<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\PlaceAdvertisement
 */
class PlaceAdvertisementResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'place' => new PlaceResource($this->whenLoaded('place')),
            'activity_type' => new ActivityTypeResource($this->whenLoaded('activityType')),
            'title' => $this->title,
            'description' => $this->description,
            'button_text' => $this->button_text,
            'media_type' => $this->media_type,
            'media_url' => $this->media_url,
            'sort_order' => $this->sort_order,
        ];
    }
}
