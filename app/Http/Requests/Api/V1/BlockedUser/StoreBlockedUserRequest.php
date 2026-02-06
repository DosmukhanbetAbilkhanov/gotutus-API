<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\BlockedUser;

use Illuminate\Foundation\Http\FormRequest;

class StoreBlockedUserRequest extends FormRequest
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
            'blocked_user_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
