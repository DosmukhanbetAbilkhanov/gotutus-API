<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Events\MessageDeletedBroadcast;
use App\Events\NewMessageBroadcast;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Message\StoreMessageRequest;
use App\Http\Resources\Api\V1\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\NotificationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class MessageController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private NotificationService $notificationService)
    {
    }

    public function index(Conversation $conversation): AnonymousResourceCollection
    {
        $this->authorize('view', $conversation);

        $userId = auth()->id();

        $messages = $conversation->messages()
            ->visibleTo($userId)
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

        // Send FCM push to the other participant
        $sender = $request->user();
        $recipient = $conversation->otherUserFor($sender);

        if ($recipient) {
            $this->notificationService->send(
                user: $recipient,
                type: 'new_message',
                title: $sender->name,
                body: \Illuminate\Support\Str::limit($message->message ?? __('message.image_sent'), 100),
                data: [
                    'conversation_id' => $conversation->id,
                    'message_id' => $message->id,
                ],
            );
        }

        return response()->json([
            'message' => __('message.sent'),
            'data' => new MessageResource($message),
        ], Response::HTTP_CREATED);
    }

    public function destroy(Request $request, Conversation $conversation, Message $message): JsonResponse
    {
        $this->authorize('view', $conversation);

        // Ensure message belongs to this conversation
        if ($message->conversation_id !== $conversation->id) {
            abort(404);
        }

        $forEveryone = $request->boolean('for_everyone', false);

        if ($forEveryone) {
            // Only message author can delete for everyone
            if ($message->user_id !== $request->user()->id) {
                return response()->json(
                    ['message' => __('message.cannot_delete_others')],
                    Response::HTTP_FORBIDDEN,
                );
            }

            $message->update([
                'deleted_at' => now(),
                'deleted_for_everyone' => true,
            ]);

            // Broadcast deletion event to conversation
            MessageDeletedBroadcast::dispatch($message, $conversation);
        } else {
            // Delete for me only - add to pivot
            $message->deletedByUsers()->syncWithoutDetaching([$request->user()->id]);
        }

        return response()->json(['message' => __('message.deleted')]);
    }
}
