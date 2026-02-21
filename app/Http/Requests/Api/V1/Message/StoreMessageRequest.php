<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Message;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
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
            'message' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'max:5120'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->filled('message') && ! $this->hasFile('image')) {
                $validator->errors()->add('message', 'A message or image is required.');
            }
        });
    }
}
