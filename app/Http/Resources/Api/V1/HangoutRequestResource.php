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
        $user = $request->user();

        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'city' => new CityResource($this->whenLoaded('city')),
            'activity_type' => new ActivityTypeResource($this->whenLoaded('activityType')),
            'place' => new PlaceResource($this->whenLoaded('place')),
            'date' => $this->date->toDateString(),
            'time' => $this->time?->format('H:i'),
            'status' => $this->status?->value,
            'notes' => $this->notes,
            'max_participants' => $this->max_participants,
            'bill_split' => $this->bill_split?->value,
            'approved_join_requests_count' => $this->approved_join_requests_count ?? null,
            'join_requests_count' => $this->whenCounted('joinRequests'),
            'is_owner' => $this->when(
                $user !== null,
                fn () => $user->id === $this->user_id
            ),
            'my_join_request' => new JoinRequestResource($this->whenLoaded('myJoinRequest')),
            'conversation_id' => $this->when(
                $user !== null,
                fn () => $this->my_conversation_id,
            ),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
