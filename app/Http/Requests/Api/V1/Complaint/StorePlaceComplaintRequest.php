<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Complaint;

use App\Enums\HangoutRequestStatus;
use Illuminate\Foundation\Http\FormRequest;

class StorePlaceComplaintRequest extends FormRequest
{
    public function authorize(): bool
    {
        $hangout = $this->route('hangoutRequest');
        $user = $this->user();

        return $user->id === $hangout->user_id
            && $hangout->status === HangoutRequestStatus::Completed
            && $hangout->place_id !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:discount_not_honored,amenities_not_provided,other'],
            'description' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }
}
