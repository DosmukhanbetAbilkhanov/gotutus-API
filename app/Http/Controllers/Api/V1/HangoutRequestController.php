<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\HangoutRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\HangoutRequest\StoreHangoutRequest;
use App\Http\Requests\Api\V1\HangoutRequest\UpdateHangoutRequest;
use App\Http\Resources\Api\V1\HangoutRequestResource;
use App\Models\HangoutRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class HangoutRequestController extends Controller
{
    /**
     * Browse hangout requests in user's city.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $query = HangoutRequest::query()
            ->with([
                'user.photos' => fn ($q) => $q->approved(),
                'city.translations',
                'activityType.translations',
                'place.translations',
            ])
            ->withCount('joinRequests')
            ->inCity($user->city_id)
            ->open()
            ->upcoming()
            ->notOwnedBy($user->id)
            ->excludeBlockedUsers($user->id)
            ->latest('date');

        // Filter by activity type
        if ($request->filled('activity_type_id')) {
            $query->forActivityType($request->input('activity_type_id'));
        }

        // Filter by date
        if ($request->filled('date')) {
            $query->forDate($request->input('date'));
        }

        $hangoutRequests = $query->paginate(15);

        return HangoutRequestResource::collection($hangoutRequests);
    }

    /**
     * Create a new hangout request.
     */
    public function store(StoreHangoutRequest $request): JsonResponse
    {
        $user = $request->user();

        $hangoutRequest = HangoutRequest::create([
            'user_id' => $user->id,
            'city_id' => $user->city_id,
            'activity_type_id' => $request->validated('activity_type_id'),
            'place_id' => $request->validated('place_id'),
            'date' => $request->validated('date'),
            'time' => $request->validated('time'),
            'notes' => $request->validated('notes'),
            'status' => HangoutRequestStatus::Open,
        ]);

        $hangoutRequest->load([
            'user',
            'city.translations',
            'activityType.translations',
            'place.translations',
        ]);

        return response()->json([
            'message' => __('hangout.created'),
            'data' => new HangoutRequestResource($hangoutRequest),
        ], Response::HTTP_CREATED);
    }

    /**
     * Show a specific hangout request.
     */
    public function show(Request $request, HangoutRequest $hangoutRequest): HangoutRequestResource|JsonResponse
    {
        Gate::authorize('view', $hangoutRequest);

        $hangoutRequest->load([
            'user.photos' => fn ($q) => $q->approved(),
            'city.translations',
            'activityType.translations',
            'place.translations',
        ]);

        // Load user's join request if exists
        $myJoinRequest = $hangoutRequest->joinRequests()
            ->where('user_id', $request->user()->id)
            ->first();

        if ($myJoinRequest) {
            $hangoutRequest->setRelation('myJoinRequest', $myJoinRequest);
        }

        return new HangoutRequestResource($hangoutRequest);
    }

    /**
     * Update own hangout request.
     */
    public function update(UpdateHangoutRequest $request, HangoutRequest $hangoutRequest): JsonResponse
    {
        Gate::authorize('update', $hangoutRequest);

        $hangoutRequest->update($request->validated());

        $hangoutRequest->load([
            'user',
            'city.translations',
            'activityType.translations',
            'place.translations',
        ]);

        return response()->json([
            'message' => __('hangout.updated'),
            'data' => new HangoutRequestResource($hangoutRequest),
        ]);
    }

    /**
     * Cancel/delete own hangout request.
     */
    public function destroy(HangoutRequest $hangoutRequest): JsonResponse
    {
        Gate::authorize('delete', $hangoutRequest);

        $hangoutRequest->update(['status' => HangoutRequestStatus::Cancelled]);

        return response()->json([
            'message' => __('hangout.cancelled'),
        ]);
    }

    /**
     * Get user's own hangout requests.
     */
    public function myRequests(Request $request): AnonymousResourceCollection
    {
        $hangoutRequests = $request->user()
            ->hangoutRequests()
            ->with([
                'city.translations',
                'activityType.translations',
                'place.translations',
            ])
            ->withCount('joinRequests')
            ->latest()
            ->paginate(15);

        return HangoutRequestResource::collection($hangoutRequests);
    }
}
