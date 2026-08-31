<?php

use App\Http\Controllers\ChatController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('chat');
    }

    $users = User::select('id', 'name', 'email')->get();

    return inertia('Welcome', [
        'users' => $users,
    ]);
})->name('welcome');

Route::post('/login/{user}', function (User $user) {
    Auth::login($user);
    return redirect()->route('chat');
})->name('login');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('welcome');
})->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/chat', [ChatController::class, 'index'])->name('chat');
    Route::get('/chat/{conversation}/messages', [ChatController::class, 'messages'])
        ->name('chat.messages');
    Route::post('/chat/{conversation}/messages', [ChatController::class, 'sendMessage'])
        ->name('chat.messages.send');
    Route::post('/chat/{conversation}/read', [ChatController::class, 'markAsRead'])
        ->name('chat.messages.read');
    Route::post('/chat/{conversation}/typing', [ChatController::class, 'typing'])
        ->name('chat.typing');
    Route::post('/chat/conversation', [ChatController::class, 'createOrGet'])
        ->name('chat.conversation.create');
});
