<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Conversation extends Model
{
    use HasFactory;

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_user')
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    public function allMessages(): HasManyThrough
    {
        return $this->hasManyThrough(Message::class, 'conversation_user');
    }

    public function getOtherUser(self $user): ?User
    {
        return $this->users->first(fn (User $u) => $u->id !== $user->id);
    }

    public function getLatestMessage(): ?Message
    {
        return $this->messages()->latest()->first();
    }

    public function getUnreadCountForUser(User $user): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    public function markAsReadForUser(User $user): void
    {
        $this->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public static function getOrCreateForUsers(array $userIds): self
    {
        $sortedUserIds = collect($userIds)->sort()->values()->all();
        $userCount = count($sortedUserIds);

        $candidates = static::whereHas('users', function ($q) use ($sortedUserIds) {
            $q->whereIn('users.id', $sortedUserIds);
        })
            ->with('users')
            ->get()
            ->filter(fn (Conversation $c) =>
                $c->users->count() === $userCount &&
                $c->users->pluck('id')->sort()->values()->all() === $sortedUserIds
            );

        $conversation = $candidates->first();

        if ($conversation) {
            return $conversation;
        }

        $conversation = static::create();
        $conversation->users()->attach($sortedUserIds);

        return $conversation;
    }
}
