<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Enums\PhotoStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\UserPhoto
 */
class UserPhotoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => '/storage/' . $this->photo_url,
            'status' => $this->status?->value,
            'rejection_reason' => $this->status === PhotoStatus::Rejected ? $this->rejection_reason : null,
            'is_approved' => $this->is_approved,
        ];
    }
}
