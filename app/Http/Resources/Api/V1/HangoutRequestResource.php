<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

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
        $user = Auth::guard('sanctum')->user();

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
                $user !== null,
                fn () => $user->id === $this->user_id
            ),
            'my_join_request' => new JoinRequestResource($this->whenLoaded('myJoinRequest')),
            'conversation_id' => $this->when(
                $user !== null,
                function () use ($user) {
                    $jr = $this->joinRequests()
                        ->where('user_id', $user->id)
                        ->whereIn('status', ['approved', 'confirmed'])
                        ->first();

                    return $jr?->conversation?->id;
                },
            ),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
