<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\BlockedUser
 */
class BlockedUserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'blocked_user' => new UserResource($this->whenLoaded('blockedUser')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
