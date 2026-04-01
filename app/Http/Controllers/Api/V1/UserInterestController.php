<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateUserInterestsRequest;
use App\Http\Resources\Api\V1\InterestResource;
use App\Models\Interest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserInterestController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $interests = $request->user()
            ->interests()
            ->with('translations')
            ->orderBy('sort_order')
            ->get();

        return InterestResource::collection($interests);
    }

    public function update(UpdateUserInterestsRequest $request): JsonResponse
    {
        $user = $request->user();
        $interestIds = $request->validated('interest_ids');

        // Only sync active, existing interest IDs
        $validIds = Interest::active()
            ->whereIn('id', $interestIds)
            ->pluck('id');

        $user->interests()->sync($validIds);

        $interests = $user->interests()
            ->with('translations')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'data' => InterestResource::collection($interests),
        ]);
    }
}
