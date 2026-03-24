<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Rating;

use App\Enums\HangoutRequestStatus;
use Illuminate\Foundation\Http\FormRequest;

class StoreHangoutRatingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $hangout = $this->route('hangoutRequest');
        $user = $this->user();

        if ($hangout->status !== HangoutRequestStatus::Completed) {
            return false;
        }

        // User must be a participant (owner or approved/confirmed joiner)
        if ($user->id === $hangout->user_id) {
            return true;
        }

        return $hangout->joinRequests()
            ->where('user_id', $user->id)
            ->whereIn('status', ['approved', 'confirmed'])
            ->exists();
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'rated_user_id' => ['required', 'integer', 'exists:users,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:500'],
        ];
    }
}
