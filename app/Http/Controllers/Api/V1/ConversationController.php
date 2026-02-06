<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ConversationResource;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class ConversationController extends Controller
{
    /**
     * List user's conversations.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $conversations = Conversation::query()
            ->with([
                'hangoutRequest.user.photos' => fn ($q) => $q->approved(),
                'hangoutRequest.activityType.translations',
                'hangoutRequest.confirmedJoinRequest.user.photos' => fn ($q) => $q->approved(),
                'latestMessage.user',
            ])
            ->whereHas('hangoutRequest', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereHas('confirmedJoinRequest', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })
            ->latest('updated_at')
            ->paginate(15);

        return ConversationResource::collection($conversations);
    }

    /**
     * Show a specific conversation.
     */
    public function show(Conversation $conversation): ConversationResource
    {
        Gate::authorize('view', $conversation);

        $conversation->load([
            'hangoutRequest.user.photos' => fn ($q) => $q->approved(),
            'hangoutRequest.activityType.translations',
            'hangoutRequest.place.translations',
            'hangoutRequest.confirmedJoinRequest.user.photos' => fn ($q) => $q->approved(),
            'latestMessage.user',
        ]);

        return new ConversationResource($conversation);
    }
}
