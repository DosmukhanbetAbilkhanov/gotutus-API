<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

/**
 * @mixin \App\Models\Message
 */
class MessageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
            'image_url' => $this->image_url ? '/storage/' . $this->image_url : null,
            'user' => new UserResource($this->whenLoaded('user')),
            'is_mine' => Auth::id() === $this->user_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
