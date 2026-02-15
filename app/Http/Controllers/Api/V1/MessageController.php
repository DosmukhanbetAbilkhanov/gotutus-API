<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

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

        $message = $conversation->messages()->create([
            'user_id' => $request->user()->id,
            'message' => $request->validated('message'),
        ]);

        $message->load('user');

        return response()->json([
            'message' => __('message.sent'),
            'data' => new MessageResource($message),
        ], Response::HTTP_CREATED);
    }
}
