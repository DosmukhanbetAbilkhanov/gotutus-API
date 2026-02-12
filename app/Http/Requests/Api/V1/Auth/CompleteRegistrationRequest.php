<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Auth;

use App\Enums\Gender;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompleteRegistrationRequest extends FormRequest
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
            'verification_token' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
            'age' => ['required', 'integer', 'min:18', 'max:100'],
            'gender' => ['required', 'string', Rule::enum(Gender::class)],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'verification_token.required' => __('validation.verification_token_required'),
            'city_id.exists' => __('validation.city_not_found'),
        ];
    }
}
