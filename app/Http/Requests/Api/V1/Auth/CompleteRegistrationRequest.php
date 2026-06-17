<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class CompleteRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'verification_token' => ['required', 'string', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            // City is derived server-side from device coordinates — never trusted
            // from a client-supplied city_id (anti-bot: real, in-city users only).
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'age' => ['nullable', 'integer', 'min:18', 'max:100'],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'public_offer_accepted' => ['required', 'accepted'],
            'public_offer_version' => ['required', 'string', 'max:20'],
        ];
    }
}
