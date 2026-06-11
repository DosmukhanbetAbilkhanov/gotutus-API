<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\User;

use App\Enums\VerificationPose;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StorePhotoVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'selfie' => ['required', 'image', 'max:5120'],
            'pose' => ['required', new Enum(VerificationPose::class)],
        ];
    }
}
