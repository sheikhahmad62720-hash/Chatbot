<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $users = [
            User::create([
                'name' => 'Ahmad Khan',
                'email' => 'ahmad@example.com',
                'password' => Hash::make('password'),
            ]),
            User::create([
                'name' => 'Sara Ali',
                'email' => 'sara@example.com',
                'password' => Hash::make('password'),
            ]),
            User::create([
                'name' => 'Hassan Raza',
                'email' => 'hassan@example.com',
                'password' => Hash::make('password'),
            ]),
            User::create([
                'name' => 'Fatima Noor',
                'email' => 'fatima@example.com',
                'password' => Hash::make('password'),
            ]),
            User::create([
                'name' => 'Usama Tariq',
                'email' => 'usama@example.com',
                'password' => Hash::make('password'),
            ]),
        ];

        $conversations = [
            Conversation::getOrCreateForUsers([$users[0]->id, $users[1]->id]),
            Conversation::getOrCreateForUsers([$users[0]->id, $users[2]->id]),
            Conversation::getOrCreateForUsers([$users[0]->id, $users[3]->id]),
            Conversation::getOrCreateForUsers([$users[1]->id, $users[2]->id]),
        ];

        $messages = [
            [$users[0]->id, "Hey Sara, how are you?"],
            [$users[1]->id, "I'm good Ahmad! Working on the new project."],
            [$users[0]->id, "That's great! Need any help?"],
            [$users[1]->id, "Yes actually, can you review the PR?"],
            [$users[0]->id, "Sure, I'll check it out this evening."],
            [$users[1]->id, "Thanks! Let me know if you have any questions."],
        ];

        foreach ($messages as $i => [$senderId, $text]) {
            Message::create([
                'conversation_id' => $conversations[0]->id,
                'sender_id' => $senderId,
                'message' => $text,
                'read_at' => $i < 4 ? now()->subMinutes(10) : null,
                'created_at' => now()->subMinutes(30 - $i * 5),
            ]);
        }

        $conv2Messages = [
            [$users[0]->id, "Hassan, did you deploy the latest build?"],
            [$users[2]->id, "Yes, deployed to staging 10 minutes ago."],
            [$users[0]->id, "Perfect, I'll run the tests now."],
        ];

        foreach ($conv2Messages as $i => [$senderId, $text]) {
            Message::create([
                'conversation_id' => $conversations[1]->id,
                'sender_id' => $senderId,
                'message' => $text,
                'read_at' => now()->subMinutes(5),
                'created_at' => now()->subMinutes(15 - $i * 5),
            ]);
        }

        $conv3Messages = [
            [$users[3]->id, "Ahmad, the design files are ready."],
            [$users[0]->id, "Awesome! I'll start implementing them tomorrow."],
        ];

        foreach ($conv3Messages as $i => [$senderId, $text]) {
            Message::create([
                'conversation_id' => $conversations[2]->id,
                'sender_id' => $senderId,
                'message' => $text,
                'read_at' => null,
                'created_at' => now()->subMinutes(3 - $i),
            ]);
        }

        $conv4Messages = [
            [$users[1]->id, "Hey Hassan, are you coming to the meeting?"],
            [$users[2]->id, "Yes, joining in 5 minutes."],
        ];

        foreach ($conv4Messages as $i => [$senderId, $text]) {
            Message::create([
                'conversation_id' => $conversations[3]->id,
                'sender_id' => $senderId,
                'message' => $text,
                'read_at' => $i === 1 ? now() : null,
                'created_at' => now()->subMinutes(20 - $i * 5),
            ]);
        }
    }
}
