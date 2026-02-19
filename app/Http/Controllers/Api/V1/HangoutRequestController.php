<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\HangoutRequest\StoreHangoutRequest;
use App\Http\Requests\Api\V1\HangoutRequest\UpdateHangoutRequest;
use App\Http\Resources\Api\V1\HangoutRequestResource;
use App\Models\HangoutRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class HangoutRequestController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): AnonymousResourceCollection
    {
        $user = Auth::guard('sanctum')->user();

        $hangouts = HangoutRequest::query()
            ->with(['user', 'city.translations', 'activityType.translations', 'place.translations'])
            ->withCount('joinRequests')
            ->open()
            ->upcoming()
            ->when($request->query('city_id'), fn ($q, $id) => $q->inCity((int) $id))
            ->when($request->query('activity_type_id'), fn ($q, $id) => $q->forActivityType((int) $id))
            ->when($request->query('date'), fn ($q, $date) => $q->forDate($date))
            ->when($user, function ($q) use ($user) {
                $blockedIds = $user->blockedUsers()->pluck('blocked_user_id');
                $blockedByIds = $user->blockedByUsers()->pluck('user_id');

                $q->where('user_id', '!=', $user->id)
                    ->whereNotIn('user_id', $blockedIds)
                    ->whereNotIn('user_id', $blockedByIds);
            })
            ->latest()
            ->paginate(20);

        return HangoutRequestResource::collection($hangouts);
    }

    public function show(HangoutRequest $hangoutRequest): HangoutRequestResource
    {
        $hangoutRequest->load(['user', 'city.translations', 'activityType.translations', 'place.translations']);

        $user = Auth::guard('sanctum')->user();
        if ($user) {
            $hangoutRequest->load(['joinRequests' => function ($q) use ($user) {
                $q->where('user_id', $user->id);
            }]);
            $hangoutRequest->setRelation(
                'myJoinRequest',
                $hangoutRequest->joinRequests->first()
            );
            $hangoutRequest->unsetRelation('joinRequests');
        }

        return new HangoutRequestResource($hangoutRequest);
    }

    public function store(StoreHangoutRequest $request): JsonResponse
    {
        $hangout = HangoutRequest::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
            'city_id' => $request->user()->city_id,
            'status' => \App\Enums\HangoutRequestStatus::Open,
        ]);

        $hangout->load(['user', 'city.translations', 'activityType.translations', 'place.translations']);

        return response()->json([
            'message' => __('hangout.created'),
            'data' => new HangoutRequestResource($hangout),
        ], Response::HTTP_CREATED);
    }

    public function update(UpdateHangoutRequest $request, HangoutRequest $hangoutRequest): JsonResponse
    {
        $this->authorize('update', $hangoutRequest);

        $hangoutRequest->update($request->validated());
        $hangoutRequest->load(['user', 'city.translations', 'activityType.translations', 'place.translations']);

        return response()->json([
            'message' => __('hangout.updated'),
            'data' => new HangoutRequestResource($hangoutRequest),
        ]);
    }

    public function destroy(HangoutRequest $hangoutRequest): JsonResponse
    {
        $this->authorize('delete', $hangoutRequest);

        $hangoutRequest->update(['status' => \App\Enums\HangoutRequestStatus::Cancelled]);

        return response()->json([
            'message' => __('hangout.cancelled'),
        ]);
    }

    public function myRequests(Request $request): AnonymousResourceCollection
    {
        $hangouts = $request->user()
            ->hangoutRequests()
            ->with(['city.translations', 'activityType.translations', 'place.translations', 'joinRequests.user'])
            ->withCount('joinRequests')
            ->latest()
            ->paginate(20);

        return HangoutRequestResource::collection($hangouts);
    }
}
