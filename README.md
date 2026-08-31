# Laravel Real-Time Chat

A single-page real-time one-to-one chat application built with Laravel, Vue 3, TypeScript, Pinia, and Laravel Reverb.

![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?style=flat&logo=laravel)
![Vue.js](https://img.shields.io/badge/Vue-3-4FC08D?style=flat&logo=vuedotjs)
![TypeScript](https://img.shields.io/badge/TypeScript-5.7-3178C6?style=flat&logo=typescript)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-3-06B6D4?style=flat&logo=tailwindcss)

## Features

- Real-time messaging via Laravel Reverb WebSockets
- One-to-one conversations
- Typing indicators
- Online/offline status with green dot indicator
- Read receipts (sent/read)
- Unread message counts
- User picker for quick demo login
- Search users and conversations
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


### Prerequisites

- PHP 8.4+
- Composer
- Node.js 18+

### Setup

```bash
git clone https://github.com/your-username/laravel-chat.git
cd laravel-chat

composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
npm run build
```

## Running the Application

Open **three terminals**:

```bash
# Terminal 1 - Laravel server
php artisan serve

# Terminal 2 - Reverb WebSocket server
php artisan reverb:start

# Terminal 3 - Vite dev server
npm run dev
```

Then visit `http://localhost:8000`. Pick any user to start chatting.

## Demo Users

| Name | Email | Password |
|------|-------|----------|
| Ahmad Khan | ahmad@example.com | password |
| Sara Ali | sara@example.com | password |
| Hassan Raza | hassan@example.com | password |
| Fatima Noor | fatima@example.com | password |
| Usama Tariq | usama@example.com | password |

## License

MIT License
