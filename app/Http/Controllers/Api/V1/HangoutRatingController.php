<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Rating\StoreHangoutRatingRequest;
use App\Http\Resources\Api\V1\HangoutRatingResource;
use App\Models\HangoutRating;
use App\Models\HangoutRequest;
use App\Services\TrustScoreService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class HangoutRatingController extends Controller
{
    public function __construct(
        private readonly TrustScoreService $trustScoreService,
    ) {}

    public function index(HangoutRequest $hangoutRequest): JsonResponse
    {
        $user = request()->user();

        $ratings = $hangoutRequest->ratings()
            ->where('rater_user_id', $user->id)
            ->get();

        return response()->json([
            'data' => HangoutRatingResource::collection($ratings),
        ]);
    }

    public function store(StoreHangoutRatingRequest $request, HangoutRequest $hangoutRequest): JsonResponse
    {
        $user = $request->user();
        $ratedUserId = $request->validated()['rated_user_id'];

        // Cannot rate self
        if ($user->id === $ratedUserId) {
            return response()->json([
                'message' => 'You cannot rate yourself.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Check for duplicate
        $existing = $hangoutRequest->ratings()
            ->where('rater_user_id', $user->id)
            ->where('rated_user_id', $ratedUserId)
            ->exists();

        if ($existing) {
            return response()->json([
                'message' => 'You already rated this participant for this hangout.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $rating = HangoutRating::create([
            'hangout_request_id' => $hangoutRequest->id,
            'rater_user_id' => $user->id,
            'rated_user_id' => $ratedUserId,
            'rating' => $request->validated()['rating'],
            'comment' => $request->validated()['comment'] ?? null,
        ]);

        // Recalculate trust score for rated user
        $ratedUser = \App\Models\User::find($ratedUserId);
        if ($ratedUser) {
            $this->trustScoreService->recalculateForUser($ratedUser);
        }

        return response()->json([
            'message' => 'Rating submitted successfully.',
            'data' => new HangoutRatingResource($rating),
        ], Response::HTTP_CREATED);
    }
}
