<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Auth;

use App\Enums\Gender;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
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
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{10,15}$/', 'unique:users,phone'],
            'age' => ['required', 'integer', 'min:18', 'max:100'],
            'gender' => ['required', 'string', Rule::enum(Gender::class)],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => __('validation.phone_format'),
            'phone.unique' => __('validation.phone_taken'),
            'city_id.exists' => __('validation.city_not_found'),
        ];
    }
}
