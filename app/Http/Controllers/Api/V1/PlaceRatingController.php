<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Rating\StorePlaceRatingRequest;
use App\Models\HangoutRequest;
use App\Models\PlaceRating;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class PlaceRatingController extends Controller
{
    public function show(HangoutRequest $hangoutRequest): JsonResponse
    {
        $user = request()->user();

        $rating = $hangoutRequest->placeRating()
            ->where('user_id', $user->id)
            ->first();

        return response()->json([
            'data' => $rating,
        ]);
    }

    public function store(StorePlaceRatingRequest $request, HangoutRequest $hangoutRequest): JsonResponse
    {
        $user = $request->user();

        // Check for duplicate
        $existing = $hangoutRequest->placeRating()
            ->where('user_id', $user->id)
            ->exists();

        if ($existing) {
            return response()->json([
                'message' => 'You already rated this place for this hangout.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Snapshot whether discount was active at hangout time
        $place = $hangoutRequest->place;
        $discountWasActive = $place?->activeDiscount !== null;

        $rating = PlaceRating::create([
            'hangout_request_id' => $hangoutRequest->id,
            'user_id' => $user->id,
            'place_id' => $hangoutRequest->place_id,
            'rating' => $request->validated()['rating'],
            'comment' => $request->validated()['comment'] ?? null,
            'discount_was_active' => $discountWasActive,
        ]);

        return response()->json([
            'message' => 'Place rating submitted successfully.',
            'data' => $rating,
        ], Response::HTTP_CREATED);
    }
}
