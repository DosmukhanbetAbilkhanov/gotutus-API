<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\JoinRequest;

use Illuminate\Foundation\Http\FormRequest;

class StoreJoinRequestRequest extends FormRequest
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
            'place_id' => ['nullable', 'integer', 'exists:places,id'],
            'message' => ['nullable', 'string', 'max:300'],
        ];
    }
}
