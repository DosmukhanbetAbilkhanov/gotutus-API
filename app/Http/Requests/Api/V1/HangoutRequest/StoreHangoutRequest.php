<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\HangoutRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class StoreHangoutRequest extends FormRequest
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
            'activity_type_id' => ['required', 'integer', 'exists:activity_types,id'],
            'goal_id' => ['required', 'integer', 'exists:goals,id'],
            'place_id' => ['nullable', 'integer', 'exists:places,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['nullable', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:500'],
            'max_participants' => ['nullable', 'integer', 'min:1', 'max:100'],
            'bill_split' => ['nullable', 'string', 'in:split_even,pay_own,organizer_pays'],
            'place_advertisement_id' => ['nullable', 'integer', 'exists:place_advertisements,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->goal_id && $this->activity_type_id) {
                $valid = DB::table('activity_type_goal')
                    ->where('activity_type_id', $this->activity_type_id)
                    ->where('goal_id', $this->goal_id)
                    ->exists();
                if (! $valid) {
                    $validator->errors()->add('goal_id', 'The selected goal does not belong to the selected activity type.');
                }
            }
        });
    }
}
