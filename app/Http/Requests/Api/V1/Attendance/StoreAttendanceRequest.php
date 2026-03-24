<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Attendance;

use App\Enums\HangoutRequestStatus;
use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $hangout = $this->route('hangoutRequest');

        return $this->user()->id === $hangout->user_id
            && $hangout->status === HangoutRequestStatus::Completed;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'attendances' => ['required', 'array', 'min:1'],
            'attendances.*.user_id' => ['required', 'integer', 'exists:users,id'],
            'attendances.*.showed_up' => ['required', 'boolean'],
        ];
    }
}
