<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\HangoutRequest
 */
class HangoutRequestResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'city' => new CityResource($this->whenLoaded('city')),
            'activity_type' => new ActivityTypeResource($this->whenLoaded('activityType')),
            'place' => new PlaceResource($this->whenLoaded('place')),
            'date' => $this->date->toDateString(),
            'time' => $this->time?->format('H:i'),
            'status' => $this->status->value,
            'notes' => $this->notes,
            'join_requests_count' => $this->whenCounted('joinRequests'),
            'is_owner' => $this->when(
                $request->user() !== null,
                fn () => $request->user()->id === $this->user_id
            ),
            'my_join_request' => new JoinRequestResource($this->whenLoaded('myJoinRequest')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
