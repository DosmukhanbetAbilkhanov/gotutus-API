<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\HangoutRequestStatus;
use App\Enums\JoinRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\JoinRequest\StoreJoinRequestRequest;
use App\Http\Resources\Api\V1\JoinRequestResource;
use App\Models\Conversation;
use App\Models\HangoutRequest;
use App\Models\JoinRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class JoinRequestController extends Controller
{
    /**
     * Send a join request to a hangout.
     */
    public function store(StoreJoinRequestRequest $request, HangoutRequest $hangoutRequest): JsonResponse
    {
        Gate::authorize('join', $hangoutRequest);

        $joinRequest = JoinRequest::create([
            'hangout_request_id' => $hangoutRequest->id,
            'user_id' => $request->user()->id,
            'place_id' => $request->validated('place_id'),
            'message' => $request->validated('message'),
            'status' => JoinRequestStatus::Pending,
        ]);

        $joinRequest->load(['user', 'place.translations']);

        return response()->json([
            'message' => __('join_request.sent'),
            'data' => new JoinRequestResource($joinRequest),
        ], Response::HTTP_CREATED);
    }

    /**
     * List join requests for a hangout (owner only).
     */
    public function index(HangoutRequest $hangoutRequest): AnonymousResourceCollection|JsonResponse
    {
        Gate::authorize('viewJoinRequests', $hangoutRequest);

        $joinRequests = $hangoutRequest->joinRequests()
            ->with([
                'user.photos' => fn ($q) => $q->approved(),
                'place.translations',
            ])
            ->latest()
            ->get();

        return JoinRequestResource::collection($joinRequests);
    }

    /**
     * Approve a join request (hangout owner).
     */
    public function approve(JoinRequest $joinRequest): JsonResponse
    {
        Gate::authorize('approve', $joinRequest);

        $joinRequest->update(['status' => JoinRequestStatus::Approved]);

        return response()->json([
            'message' => __('join_request.approved'),
            'data' => new JoinRequestResource($joinRequest->load('user')),
        ]);
    }

    /**
     * Decline a join request (hangout owner).
     */
    public function decline(JoinRequest $joinRequest): JsonResponse
    {
        Gate::authorize('decline', $joinRequest);

        $joinRequest->update(['status' => JoinRequestStatus::Declined]);

        return response()->json([
            'message' => __('join_request.declined'),
        ]);
    }

    /**
     * Confirm participation after approval (join requester).
     */
    public function confirm(JoinRequest $joinRequest): JsonResponse
    {
        Gate::authorize('confirm', $joinRequest);

        DB::transaction(function () use ($joinRequest) {
            // Update join request
            $joinRequest->update([
                'status' => JoinRequestStatus::Confirmed,
                'confirmed_at' => now(),
            ]);

            // Update hangout status to matched
            $joinRequest->hangoutRequest->update([
                'status' => HangoutRequestStatus::Matched,
            ]);

            // Create conversation for messaging
            Conversation::firstOrCreate([
                'hangout_request_id' => $joinRequest->hangout_request_id,
            ]);
        });

        return response()->json([
            'message' => __('join_request.confirmed'),
            'data' => new JoinRequestResource($joinRequest->load(['user', 'hangoutRequest'])),
        ]);
    }

    /**
     * Cancel own join request.
     */
    public function cancel(JoinRequest $joinRequest): JsonResponse
    {
        Gate::authorize('cancel', $joinRequest);

        $joinRequest->update(['status' => JoinRequestStatus::Cancelled]);

        return response()->json([
            'message' => __('join_request.cancelled'),
        ]);
    }

    /**
     * Get user's sent join requests.
     */
    public function myJoinRequests(Request $request): AnonymousResourceCollection
    {
        $joinRequests = $request->user()
            ->joinRequests()
            ->with([
                'hangoutRequest.user.photos' => fn ($q) => $q->approved(),
                'hangoutRequest.activityType.translations',
                'hangoutRequest.city.translations',
                'place.translations',
            ])
            ->latest()
            ->paginate(15);

        return JoinRequestResource::collection($joinRequests);
    }
}
