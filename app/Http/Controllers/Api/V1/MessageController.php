<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Message\StoreMessageRequest;
use App\Http\Resources\Api\V1\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class MessageController extends Controller
{
    /**
     * List messages in a conversation.
     */
    public function index(Conversation $conversation): AnonymousResourceCollection
    {
        Gate::authorize('view', $conversation);

        $messages = $conversation->messages()
            ->with(['user.photos' => fn ($q) => $q->approved()])
            ->latest()
            ->paginate(50);

        return MessageResource::collection($messages);
    }

    /**
     * Send a message in a conversation.
     */
    public function store(StoreMessageRequest $request, Conversation $conversation): JsonResponse
    {
        Gate::authorize('sendMessage', $conversation);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $request->user()->id,
            'message' => $request->validated('message'),
        ]);

        // Update conversation timestamp
        $conversation->touch();

        $message->load(['user']);

        return response()->json([
            'message' => __('message.sent'),
            'data' => new MessageResource($message),
        ], Response::HTTP_CREATED);
    }
}
