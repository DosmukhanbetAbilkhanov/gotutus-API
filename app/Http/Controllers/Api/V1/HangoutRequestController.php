<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\HangoutRequestStatus;
use App\Enums\JoinRequestStatus;
use App\Events\NewHangoutBroadcast;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\HangoutRequest\StoreHangoutRequest;
use App\Http\Requests\Api\V1\HangoutRequest\UpdateHangoutRequest;
use App\Http\Resources\Api\V1\HangoutRequestResource;
use App\Models\Conversation;
use App\Models\HangoutRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class HangoutRequestController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): AnonymousResourceCollection
    {
        $user = Auth::guard('sanctum')->user();

        $hangouts = HangoutRequest::query()
            ->with(['user.photos' => fn ($q) => $q->where('status', 'approved'), 'city.translations', 'activityType.translations', 'goal.translations', 'place.translations', 'place.activeDiscount', 'place.activePromotions', 'place.workingHours'])
            ->withCount('joinRequests')
            ->withCount(['joinRequests as approved_join_requests_count' => function ($q) {
                $q->whereIn('status', ['approved', 'confirmed']);
            }])
            ->open()
            ->upcoming()
            ->when($request->query('city_id'), fn ($q, $id) => $q->inCity((int) $id))
            ->when($request->query('activity_type_id'), fn ($q, $id) => $q->forActivityType((int) $id))
            ->when($request->query('goal_id'), fn ($q, $id) => $q->where('goal_id', (int) $id))
            ->when($request->query('date'), fn ($q, $date) => $q->forDate($date))
            ->when($request->query('gender'), function ($q, $gender) {
                $q->whereHas('user', fn ($u) => $u->where('gender', $gender));
            })
            ->when($request->query('min_age'), function ($q, $minAge) {
                $q->whereHas('user', fn ($u) => $u->where('age', '>=', (int) $minAge));
            })
            ->when($request->query('max_age'), function ($q, $maxAge) {
                $q->whereHas('user', fn ($u) => $u->where('age', '<=', (int) $maxAge));
            })
            ->when($user, function ($q) use ($user) {
                // Cache blocked user IDs for 5 minutes to reduce DB queries
                $blockedIds = cache()->remember(
                    "user.{$user->id}.blocked_ids",
                    now()->addMinutes(5),
                    fn () => $user->blockedUsers()->pluck('blocked_user_id')->toArray()
                );

                $blockedByIds = cache()->remember(
                    "user.{$user->id}.blocked_by_ids",
                    now()->addMinutes(5),
                    fn () => $user->blockedByUsers()->pluck('user_id')->toArray()
                );

                $q->where('user_id', '!=', $user->id)
                    ->whereNotIn('user_id', $blockedIds)
                    ->whereNotIn('user_id', $blockedByIds)
                    ->addSelect(['my_conversation_id' => Conversation::query()
                        ->select('conversations.id')
                        ->join('join_requests', 'join_requests.id', '=', 'conversations.join_request_id')
                        ->whereColumn('join_requests.hangout_request_id', 'hangout_requests.id')
                        ->where('join_requests.user_id', $user->id)
                        ->whereIn('join_requests.status', ['approved', 'confirmed'])
                        ->limit(1),
                    ])
                    ->addSelect(['group_conversation_id' => Conversation::query()
                        ->select('conversations.id')
                        ->whereColumn('conversations.hangout_request_id', 'hangout_requests.id')
                        ->whereNull('conversations.join_request_id')
                        ->whereExists(fn ($sub) => $sub->select(DB::raw(1))
                            ->from('conversation_user')
                            ->whereColumn('conversation_user.conversation_id', 'conversations.id')
                            ->where('conversation_user.user_id', $user->id))
                        ->limit(1),
                    ]);
            })
            ->latest()
            ->paginate(20);

        return HangoutRequestResource::collection($hangouts);
    }

    public function show(HangoutRequest $hangoutRequest): HangoutRequestResource
    {
        $hangoutRequest->load(['user.photos' => fn ($q) => $q->where('status', 'approved'), 'city.translations', 'activityType.translations', 'goal.translations', 'place.translations', 'place.activeDiscount', 'place.activePromotions', 'place.workingHours']);
        $hangoutRequest->loadCount(['joinRequests as approved_join_requests_count' => function ($q) {
            $q->whereIn('status', ['approved', 'confirmed']);
        }]);

        $user = Auth::guard('sanctum')->user();
        if ($user) {
            $hangoutRequest->load(['joinRequests' => function ($q) use ($user) {
                $q->where('user_id', $user->id)->with('conversation');
            }]);
            $hangoutRequest->setRelation(
                'myJoinRequest',
                $hangoutRequest->joinRequests->first()
            );
            $hangoutRequest->unsetRelation('joinRequests');

            // Set conversation_id via the already-loaded join request
            $myJr = $hangoutRequest->myJoinRequest;
            $hangoutRequest->my_conversation_id = $myJr?->conversation?->id;

            // Group conversation id (only when the viewer is a confirmed participant)
            $hangoutRequest->group_conversation_id = $hangoutRequest->groupConversation()
                ->whereHas('participants', fn ($p) => $p->where('users.id', $user->id))
                ->value('id');
        }

        return new HangoutRequestResource($hangoutRequest);
    }

    public function store(StoreHangoutRequest $request): JsonResponse
    {
        $hangout = HangoutRequest::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
            'city_id' => $request->user()->city_id,
            'status' => HangoutRequestStatus::Open,
        ]);

        $hangout->load(['user.photos' => fn ($q) => $q->where('status', 'approved'), 'city.translations', 'activityType.translations', 'goal.translations', 'place.translations', 'place.activeDiscount', 'place.activePromotions', 'place.workingHours']);

        NewHangoutBroadcast::dispatch($hangout);

        return response()->json([
            'message' => __('hangout.created'),
            'data' => new HangoutRequestResource($hangout),
        ], Response::HTTP_CREATED);
    }

    public function update(UpdateHangoutRequest $request, HangoutRequest $hangoutRequest): JsonResponse
    {
        $this->authorize('update', $hangoutRequest);

        $hangoutRequest->update($request->validated());
        $hangoutRequest->load(['user.photos' => fn ($q) => $q->where('status', 'approved'), 'city.translations', 'activityType.translations', 'goal.translations', 'place.translations', 'place.activeDiscount', 'place.activePromotions', 'place.workingHours']);

        return response()->json([
            'message' => __('hangout.updated'),
            'data' => new HangoutRequestResource($hangoutRequest),
        ]);
    }

    public function destroy(HangoutRequest $hangoutRequest): JsonResponse
    {
        $this->authorize('delete', $hangoutRequest);

        $hangoutRequest->update(['status' => HangoutRequestStatus::Cancelled]);

        return response()->json([
            'message' => __('hangout.cancelled'),
        ]);
    }

    public function close(HangoutRequest $hangoutRequest): JsonResponse
    {
        $this->authorize('update', $hangoutRequest);

        if ($hangoutRequest->status !== HangoutRequestStatus::Open) {
            return response()->json([
                'message' => __('hangout.invalid_status_transition'),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::transaction(function () use ($hangoutRequest) {
            $hangoutRequest->update(['status' => HangoutRequestStatus::Closed]);

            // Auto-decline all pending join requests
            $hangoutRequest->joinRequests()
                ->where('status', JoinRequestStatus::Pending)
                ->update(['status' => JoinRequestStatus::Declined->value]);
        });

        $hangoutRequest->load(['user.photos' => fn ($q) => $q->where('status', 'approved'), 'city.translations', 'activityType.translations', 'goal.translations', 'place.translations', 'place.activeDiscount', 'place.activePromotions', 'place.workingHours']);
        $hangoutRequest->loadCount(['joinRequests as approved_join_requests_count' => function ($q) {
            $q->whereIn('status', ['approved', 'confirmed']);
        }]);

        return response()->json([
            'message' => __('hangout.closed'),
            'data' => new HangoutRequestResource($hangoutRequest),
        ]);
    }

    public function complete(HangoutRequest $hangoutRequest): JsonResponse
    {
        $this->authorize('update', $hangoutRequest);

        if (! in_array($hangoutRequest->status, [
            HangoutRequestStatus::Open,
            HangoutRequestStatus::Closed,
            HangoutRequestStatus::Matched,
        ])) {
            return response()->json([
                'message' => __('hangout.invalid_status_transition'),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $hangoutRequest->update(['status' => HangoutRequestStatus::Completed]);

        $hangoutRequest->load(['user.photos' => fn ($q) => $q->where('status', 'approved'), 'city.translations', 'activityType.translations', 'goal.translations', 'place.translations', 'place.activeDiscount', 'place.activePromotions', 'place.workingHours']);
        $hangoutRequest->loadCount(['joinRequests as approved_join_requests_count' => function ($q) {
            $q->whereIn('status', ['approved', 'confirmed']);
        }]);

        return response()->json([
            'message' => __('hangout.completed'),
            'data' => new HangoutRequestResource($hangoutRequest),
        ]);
    }

    public function myRequests(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $hangouts = $user
            ->hangoutRequests()
            ->select('hangout_requests.*')
            ->with(['city.translations', 'activityType.translations', 'goal.translations', 'place.translations', 'place.activeDiscount', 'place.activePromotions', 'place.workingHours', 'joinRequests.user.photos' => fn ($q) => $q->where('status', 'approved')])
            ->withCount('joinRequests')
            ->withCount(['joinRequests as approved_join_requests_count' => function ($q) {
                $q->whereIn('status', ['approved', 'confirmed']);
            }])
            ->addSelect(['my_conversation_id' => Conversation::query()
                ->select('conversations.id')
                ->join('join_requests', 'join_requests.id', '=', 'conversations.join_request_id')
                ->whereColumn('join_requests.hangout_request_id', 'hangout_requests.id')
                ->where('join_requests.user_id', $user->id)
                ->whereIn('join_requests.status', ['approved', 'confirmed'])
                ->limit(1),
            ])
            ->addSelect(['group_conversation_id' => Conversation::query()
                ->select('conversations.id')
                ->whereColumn('conversations.hangout_request_id', 'hangout_requests.id')
                ->whereNull('conversations.join_request_id')
                ->whereExists(fn ($sub) => $sub->select(DB::raw(1))
                    ->from('conversation_user')
                    ->whereColumn('conversation_user.conversation_id', 'conversations.id')
                    ->where('conversation_user.user_id', $user->id))
                ->limit(1),
            ])
            ->latest()
            ->paginate(20);

        return HangoutRequestResource::collection($hangouts);
    }
}
