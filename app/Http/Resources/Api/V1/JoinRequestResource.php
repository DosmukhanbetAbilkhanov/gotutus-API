<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\JoinRequest
 */
class JoinRequestResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'message' => $this->message,
            'confirmed_at' => $this->confirmed_at?->toIso8601String(),
            'user' => new UserResource($this->whenLoaded('user')),
            'place' => new PlaceResource($this->whenLoaded('place')),
            'hangout_request' => new HangoutRequestResource($this->whenLoaded('hangoutRequest')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
