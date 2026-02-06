<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\User
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isOwner = $request->user()?->id === $this->id;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->when($isOwner, $this->phone),
            'email' => $this->when($isOwner, $this->email),
            'city' => new CityResource($this->whenLoaded('city')),
            'photos' => UserPhotoResource::collection($this->whenLoaded('photos')),
            'phone_verified' => $this->isPhoneVerified(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
