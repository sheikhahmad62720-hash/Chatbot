# Laravel Real-Time Chat

A single-page real-time one-to-one chat application built with Laravel, Vue 3, TypeScript, Pinia, and Laravel Reverb.

![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?style=flat&logo=laravel)
![Vue.js](https://img.shields.io/badge/Vue-3-4FC08D?style=flat&logo=vuedotjs)
![TypeScript](https://img.shields.io/badge/TypeScript-5.7-3178C6?style=flat&logo=typescript)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-3-06B6D4?style=flat&logo=tailwindcss)

## Features

- Real-time messaging with Laravel Reverb
- One-to-one conversations
- Typing indicators
- Online/offline status
- Read receipts (sent/read)
- Unread message counts
- Message history with timestamps
- User search
- Responsive design (mobile + desktop)
- Auto-scroll to newest messages
- Date separators between message groups

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 13, PHP 8.4 |
| Frontend | Vue 3 (Composition API), TypeScript |
| State | Pinia |
| Real-time | Laravel Reverb, Laravel Echo, Pusher.js |
| Styling | Tailwind CSS |
| Database | SQLite |
| Routing | Inertia.js |

## Architecture

```
Vue Component
    ↓
Pinia Store (state management)
    ↓
HTTP Request (axios)
    ↓
Laravel Controller
    ↓
Eloquent Model → SQLite Database
    ↓
Broadcast Event (MessageSent)
    ↓
Laravel Reverb (WebSocket server)
    ↓
Laravel Echo (client listener)
    ↓
Vue Component (updates UI)
```

## Database Structure

```
users
├── id
├── name
├── email
├── password
├── created_at
└── updated_at

conversations
├── id
├── created_at
└── updated_at

conversation_user (pivot)
├── conversation_id → conversations.id
├── user_id → users.id
├── created_at
└── updated_at

messages
├── id
├── conversation_id → conversations.id
├── sender_id → users.id
├── message (text)
├── read_at (nullable datetime)
├── created_at
└── updated_at
```

### Eloquent Relationships

```
User
 ├── conversations()         → BelongsToMany (via pivot)
 └── sentMessages()          → HasMany

Conversation
 ├── users()                 → BelongsToMany (via pivot)
 └── messages()              → HasMany

Message
 ├── conversation()          → BelongsTo
 └── sender()                → BelongsTo (User)
```

## Project Structure

```
app/
├── Events/
│   ├── MessageSent.php
│   ├── MessageRead.php
│   ├── TypingEvent.php
│   └── UserOnline.php
├── Http/Controllers/
│   └── ChatController.php
├── Models/
│   ├── User.php
│   ├── Conversation.php
│   └── Message.php
└── Policies/
    └── ConversationPolicy.php

resources/js/
├── components/chat/
│   ├── ChatSidebar.vue
│   ├── ChatHeader.vue
│   ├── MessageList.vue
│   ├── MessageBubble.vue
│   └── MessageInput.vue
├── composables/
│   └── useChat.ts
├── stores/
│   └── chatStore.ts
├── types/
│   └── chat.ts
├── Pages/
│   └── Chat.vue
└── app.js
```

## Installation

### Prerequisites

- PHP 8.4+
- Composer
- Node.js 18+

### Setup

```bash
# Clone the repository
git clone https://github.com/your-username/laravel-chat.git
cd laravel-chat

# Install PHP dependencies
composer install

# Install JS dependencies
npm install

# Create environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create SQLite database
touch database/database.sqlite

# Run migrations and seed demo data
php artisan migrate:fresh --seed

# Build frontend assets
npm run build
```

## Running the Application

Open **three terminals** and run each command:

```bash
# Terminal 1 - Laravel server
php artisan serve

# Terminal 2 - Reverb WebSocket server
php artisan reverb:start

# Terminal 3 - Vite dev server (for development)
npm run dev
```

Then visit `http://localhost:8000`.

## Environment Variables

```env
# Broadcasting
BROADCAST_CONNECTION=reverb

# Reverb
REVERB_APP_KEY=local
REVERB_APP_SECRET=local
REVERB_APP_ID=local
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

# Frontend Reverb
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

## Demo Users

After seeding, the following users are available:

| Name | Email | Password |
|------|-------|----------|
| Ahmad Khan | ahmad@example.com | password |
| Sara Ali | sara@example.com | password |
| Hassan Raza | hassan@example.com | password |
| Fatima Noor | fatima@example.com | password |
| Usama Tariq | usama@example.com | password |

Since the app skips authentication for demo purposes, you can switch users by modifying the seeded user or using multiple browser tabs.

## API Routes

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/chat` | Main chat page (Inertia) |
| `GET` | `/chat/{conversation}/messages` | Get messages for a conversation |
| `POST` | `/chat/{conversation}/messages` | Send a new message |
| `POST` | `/chat/{conversation}/read` | Mark messages as read |
| `POST` | `/chat/{conversation}/typing` | Send typing indicator |
| `POST` | `/chat/conversation` | Create or get conversation with user |

## Broadcast Events

| Event | Channel | Description |
|-------|---------|-------------|
| `MessageSent` | `private-conversation.{id}` | New message broadcast |
| `MessageRead` | `private-conversation.{id}` | Read receipt broadcast |
| `TypingEvent` | `private-conversation.{id}` | Typing indicator |
| `UserOnline` | `private-users.online` | User online status |

## Screenshots

> Add screenshots of the application here

```
screenshots/
├── chat-overview.png
├── mobile-view.png
├── typing-indicator.png
└── unread-messages.png
```

## Future Improvements

- [ ] Group conversations
- [ ] File and image sharing
- [ ] Message editing and deletion
- [ ] User authentication (login/register)
- [ ] User profile and avatar upload
- [ ] Message search
- [ ] Emoji picker
- [ ] Message reactions
- [ ] Voice messages
- [ ] Push notifications
- [ ] Message pagination for large histories
- [ ] Presence channels for accurate online status

## License

MIT License
