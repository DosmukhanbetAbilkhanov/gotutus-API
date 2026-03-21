<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\HangoutRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHangoutRequest extends FormRequest
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
            'activity_type_id' => ['sometimes', 'integer', 'exists:activity_types,id'],
            'place_id' => ['nullable', 'integer', 'exists:places,id'],
            'date' => ['sometimes', 'date', 'after_or_equal:today'],
            'time' => ['nullable', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:500'],
            'max_participants' => ['nullable', 'integer', 'min:1', 'max:100'],
            'bill_split' => ['nullable', 'string', 'in:split_even,pay_own,organizer_pays'],
        ];
    }
}
