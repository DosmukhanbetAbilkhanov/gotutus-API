<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,'.$this->user()->id],
            'city_id' => ['sometimes', 'integer', 'exists:cities,id'],
            'age' => ['sometimes', 'integer', 'min:18', 'max:100'],
            'bio' => ['nullable', 'string', 'max:500'],
            // gender is intentionally excluded - locked after registration
        ];
    }
}
