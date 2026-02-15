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
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^\+7[0-9]{10}$/', 'unique:users,phone'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'age' => ['nullable', 'integer', 'min:18', 'max:100'],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
        ];
    }
}
