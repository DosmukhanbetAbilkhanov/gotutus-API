<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\AppDesignSetting
 */
class AppDesignSettingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'colors' => $this->colors,
            'typography' => $this->typography,
            'spacing' => $this->spacing,
            'border_radius' => $this->border_radius,
            'version' => $this->version,
        ];
    }
}
