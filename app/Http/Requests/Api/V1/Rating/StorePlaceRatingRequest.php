<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Rating;

use App\Enums\HangoutRequestStatus;
use Illuminate\Foundation\Http\FormRequest;

class StorePlaceRatingRequest extends FormRequest
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
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:500'],
        ];
    }
}
