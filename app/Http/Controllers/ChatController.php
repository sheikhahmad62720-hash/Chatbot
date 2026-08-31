<?php

namespace App\Http\Controllers;

use App\Events\MessageRead;
use App\Events\MessageSent;
use App\Events\TypingEvent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $conversations = Conversation::whereHas('users', function ($q) use ($user) {
            $q->where('users.id', $user->id);
        })
            ->with(['users' => function ($q) {
                $q->select('users.id', 'users.name', 'users.email');
            }])
            ->with(['messages' => function ($q) {
                $q->latest()->limit(1)->with('sender:id,name');
            }])
            ->get()
            ->map(function (Conversation $conversation) use ($user) {
                $otherUser = $conversation->getOtherUser($user);
                $latestMessage = $conversation->messages->first();

                return [
                    'id' => $conversation->id,
                    'other_user' => $otherUser ? [
                        'id' => $otherUser->id,
                        'name' => $otherUser->name,
                        'email' => $otherUser->email,
                    ] : null,
                    'last_message' => $latestMessage ? [
                        'id' => $latestMessage->id,
                        'message' => $latestMessage->message,
                        'sender_id' => $latestMessage->sender_id,
                        'created_at' => $latestMessage->created_at->toISOString(),
                        'sender' => [
                            'id' => $latestMessage->sender->id,
                            'name' => $latestMessage->sender->name,
                        ],
                    ] : null,
                    'unread_count' => $conversation->getUnreadCountForUser($user),
                    'updated_at' => $conversation->updated_at->toISOString(),
                ];
            })
            ->sortByDesc(fn ($c) => $c['last_message'] ? $c['last_message']['created_at'] : $c['updated_at'])
            ->values();

        $users = User::where('id', '!=', $user->id)
            ->select('id', 'name', 'email')
            ->get();

        return inertia('Chat', [
            'conversations' => $conversations,
            'users' => $users,
            'auth' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    public function messages(Request $request, Conversation $conversation): JsonResponse
    {
        Gate::authorize('view', $conversation);

        $messages = $conversation->messages()
            ->with('sender:id,name')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn (Message $message) => [
                'id' => $message->id,
                'message' => $message->message,
                'sender_id' => $message->sender_id,
                'conversation_id' => $message->conversation_id,
                'read_at' => $message->read_at?->toISOString(),
                'created_at' => $message->created_at->toISOString(),
                'updated_at' => $message->updated_at->toISOString(),
                'sender' => [
                    'id' => $message->sender->id,
                    'name' => $message->sender->name,
                ],
            ]);

        return response()->json(['messages' => $messages]);
    }

    public function sendMessage(Request $request, Conversation $conversation): JsonResponse
    {
        Gate::authorize('sendMessage', $conversation);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $message = $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'message' => $validated['message'],
        ]);

        $message->load('sender:id,name');

        broadcast(new MessageSent($message, $conversation))->toOthers();

        $conversation->touch();

        return response()->json([
            'message' => [
                'id' => $message->id,
                'message' => $message->message,
                'sender_id' => $message->sender_id,
                'conversation_id' => $message->conversation_id,
                'read_at' => null,
                'created_at' => $message->created_at->toISOString(),
                'updated_at' => $message->updated_at->toISOString(),
                'sender' => [
                    'id' => $message->sender->id,
                    'name' => $message->sender->name,
                ],
            ],
        ]);
    }

    public function markAsRead(Request $request, Conversation $conversation): JsonResponse
    {
        Gate::authorize('readMessages', $conversation);

        $conversation->markAsReadForUser($request->user());

        broadcast(new MessageRead($request->user(), $conversation))->toOthers();

        return response()->json(['status' => 'read']);
    }

    public function typing(Request $request, Conversation $conversation): JsonResponse
    {
        Gate::authorize('sendMessage', $conversation);

        $validated = $request->validate([
            'is_typing' => ['required', 'boolean'],
        ]);

        broadcast(new TypingEvent(
            $request->user(),
            $conversation,
            $validated['is_typing'],
        ))->toOthers();

        return response()->json(['status' => 'ok']);
    }

    public function createOrGet(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $otherUser = User::findOrFail($validated['user_id']);

        if ($otherUser->id === $request->user()->id) {
            throw ValidationException::withMessages([
                'user_id' => 'You cannot start a conversation with yourself.',
            ]);
        }

        $conversation = Conversation::getOrCreateForUsers([
            $request->user()->id,
            $otherUser->id,
        ]);

        $otherUserModel = $conversation->getOtherUser($request->user());
        $latestMessage = $conversation->getLatestMessage();

        return response()->json([
            'conversation' => [
                'id' => $conversation->id,
                'other_user' => $otherUserModel ? [
                    'id' => $otherUserModel->id,
                    'name' => $otherUserModel->name,
                    'email' => $otherUserModel->email,
                ] : null,
                'last_message' => $latestMessage ? [
                    'id' => $latestMessage->id,
                    'message' => $latestMessage->message,
                    'sender_id' => $latestMessage->sender_id,
                    'created_at' => $latestMessage->created_at->toISOString(),
                    'sender' => [
                        'id' => $latestMessage->sender->id,
                        'name' => $latestMessage->sender->name,
                    ],
                ] : null,
                'unread_count' => $conversation->getUnreadCountForUser($request->user()),
                'updated_at' => $conversation->updated_at->toISOString(),
            ],
        ]);
    }
}
