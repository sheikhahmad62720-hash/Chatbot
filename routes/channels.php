<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::find($conversationId);

    if ($conversation && $conversation->users->contains('id', $user->id)) {
        return true;
    }

    return false;
});

Broadcast::channel('users.online', function ($user) {
    return [
        'id' => $user->id,
        'name' => $user->name,
    ];
});
