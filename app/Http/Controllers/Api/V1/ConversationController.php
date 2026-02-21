<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Events\TypingBroadcast;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ConversationResource;
use App\Models\Conversation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ConversationController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $conversations = Conversation::query()
            ->where(function ($q) use ($user) {
                $q->whereHas('hangoutRequest', fn ($hq) => $hq->where('user_id', $user->id))
                    ->orWhereHas('joinRequest', fn ($jq) => $jq->where('user_id', $user->id));
            })
            ->with([
                'hangoutRequest.user.photos',
                'hangoutRequest.activityType.translations',
                'joinRequest.user.photos',
                'latestMessage',
                'participants',
            ])
            ->latest('updated_at')
            ->paginate(20);

        return ConversationResource::collection($conversations);
    }

    public function show(Conversation $conversation): ConversationResource
    {
        $this->authorize('view', $conversation);

        $conversation->load([
            'hangoutRequest.user.photos',
            'hangoutRequest.activityType.translations',
            'joinRequest.user.photos',
            'messages.user',
            'participants',
        ]);

        return new ConversationResource($conversation);
    }

    public function markAsRead(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);

        $conversation->markAsReadFor($request->user()->id);

        return response()->json(['message' => 'ok']);
    }

    public function typing(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);

        TypingBroadcast::dispatch($conversation->id, $request->user()->id);

        return response()->json(['message' => 'ok']);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();

        $conversations = Conversation::query()
            ->where(function ($q) use ($user) {
                $q->whereHas('hangoutRequest', fn ($hq) => $hq->where('user_id', $user->id))
                    ->orWhereHas('joinRequest', fn ($jq) => $jq->where('user_id', $user->id));
            })
            ->with('participants')
            ->get();

        $total = $conversations->sum(fn (Conversation $c) => $c->unreadCountFor($user->id));

        return response()->json(['unread_count' => $total]);
    }
}
