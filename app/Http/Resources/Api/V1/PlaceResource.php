<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Place
 */
class PlaceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'city' => new CityResource($this->whenLoaded('city')),
            'discount' => $this->when($this->relationLoaded('activeDiscount') && $this->activeDiscount, function () {
                return [
                    'percent' => $this->activeDiscount->discount_percent,
                ];
            }),
            'activity_types' => ActivityTypeResource::collection($this->whenLoaded('activityTypes')),
            'working_hours' => $this->when($this->relationLoaded('workingHours'), function () {
                return $this->workingHours->map(function ($wh) {
                    return [
                        'day_of_week' => $wh->day_of_week,
                        'open_time' => $wh->open_time,
                        'close_time' => $wh->close_time,
                    ];
                });
            }),
        ];
    }
}
