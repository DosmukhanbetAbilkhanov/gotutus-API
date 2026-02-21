<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Events\NewMessageBroadcast;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Message\StoreMessageRequest;
use App\Http\Resources\Api\V1\MessageResource;
use App\Models\Conversation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class MessageController extends Controller
{
    use AuthorizesRequests;

    public function index(Conversation $conversation): AnonymousResourceCollection
    {
        $this->authorize('view', $conversation);

        $messages = $conversation->messages()
            ->with('user')
            ->latest('created_at')
            ->paginate(50);

        return MessageResource::collection($messages);
    }

    public function store(StoreMessageRequest $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('sendMessage', $conversation);

        $data = [
            'user_id' => $request->user()->id,
            'message' => $request->validated('message'),
        ];

        if ($request->hasFile('image')) {
            $data['image_url'] = $request->file('image')->store('message-images', 'public');
        }

        $message = $conversation->messages()->create($data);

        $message->load('user');

        // Mark conversation as read for the sender
        $conversation->markAsReadFor($request->user()->id);

        NewMessageBroadcast::dispatch($message);

        return response()->json([
            'message' => __('message.sent'),
            'data' => new MessageResource($message),
        ], Response::HTTP_CREATED);
    }
}
