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
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class JoinRequestController extends Controller
{
    use AuthorizesRequests;

    public function index(HangoutRequest $hangoutRequest): AnonymousResourceCollection
    {
        $joinRequests = $hangoutRequest->joinRequests()
            ->with(['user', 'place.translations'])
            ->latest()
            ->get();

        return JoinRequestResource::collection($joinRequests);
    }

    public function store(StoreJoinRequestRequest $request, HangoutRequest $hangoutRequest): JsonResponse
    {
        $joinRequest = $hangoutRequest->joinRequests()->create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        $joinRequest->load(['user', 'place.translations']);

        return response()->json([
            'message' => __('join_request.sent'),
            'data' => new JoinRequestResource($joinRequest),
        ], Response::HTTP_CREATED);
    }

    public function approve(JoinRequest $joinRequest): JsonResponse
    {
        $this->authorize('approve', $joinRequest);

        $joinRequest->update(['status' => JoinRequestStatus::Approved]);

        return response()->json([
            'message' => __('join_request.approved'),
            'data' => new JoinRequestResource($joinRequest->fresh()->load(['user', 'place.translations'])),
        ]);
    }

    public function decline(JoinRequest $joinRequest): JsonResponse
    {
        $this->authorize('decline', $joinRequest);

        $joinRequest->update(['status' => JoinRequestStatus::Declined]);

        return response()->json([
            'message' => __('join_request.declined'),
        ]);
    }

    public function confirm(JoinRequest $joinRequest): JsonResponse
    {
        $this->authorize('confirm', $joinRequest);

        $joinRequest->update([
            'status' => JoinRequestStatus::Confirmed,
            'confirmed_at' => now(),
        ]);

        $hangout = $joinRequest->hangoutRequest;
        $hangout->update(['status' => HangoutRequestStatus::Matched]);

        Conversation::create(['hangout_request_id' => $hangout->id]);

        return response()->json([
            'message' => __('join_request.confirmed'),
        ]);
    }

    public function cancel(JoinRequest $joinRequest): JsonResponse
    {
        $this->authorize('cancel', $joinRequest);

        $joinRequest->update(['status' => JoinRequestStatus::Cancelled]);

        return response()->json([
            'message' => __('join_request.cancelled'),
        ]);
    }

    public function myJoinRequests(Request $request): AnonymousResourceCollection
    {
        $joinRequests = $request->user()
            ->joinRequests()
            ->with(['hangoutRequest.user', 'hangoutRequest.activityType.translations', 'place.translations'])
            ->latest()
            ->paginate(20);

        return JoinRequestResource::collection($joinRequests);
    }
}
