<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ConversationResource;
use App\Models\Conversation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ConversationController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $conversations = Conversation::query()
            ->whereHas('hangoutRequest', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('joinRequests', fn ($jq) => $jq->confirmed()->where('user_id', $user->id));
            })
            ->with(['hangoutRequest.user', 'hangoutRequest.activityType.translations', 'latestMessage'])
            ->latest()
            ->paginate(20);

        return ConversationResource::collection($conversations);
    }

    public function show(Conversation $conversation): ConversationResource
    {
        $this->authorize('view', $conversation);

        $conversation->load(['hangoutRequest.user', 'hangoutRequest.activityType.translations', 'messages.user']);

        return new ConversationResource($conversation);
    }
}
