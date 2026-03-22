<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\NewsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes — News Platform
|--------------------------------------------------------------------------
*/

Route::get('/', [NewsController::class, 'index'])->name('news.index');
Route::get('/news', [NewsController::class, 'index'])->name('news.list');
Route::get('/article/{id}', [NewsController::class, 'show'])->name('news.show');

/*
|--------------------------------------------------------------------------
| Hidden Auth Routes (no middleware — these are the gate)
|--------------------------------------------------------------------------
*/
Route::prefix('_h')->name('hidden.')->group(function () {
    Route::get('/click-count',    [AuthController::class, 'clickCount'])->name('click-count');
    Route::post('/login',         [AuthController::class, 'login'])->name('login');
    Route::post('/logout',        [AuthController::class, 'logout'])->name('logout');
    Route::get('/session-check',  [AuthController::class, 'checkSession'])->name('session-check');
});

/*
|--------------------------------------------------------------------------
| Chat Routes — Protected by HiddenAuth
|--------------------------------------------------------------------------
*/
Route::prefix('chat')->name('chat.')->middleware('hidden.auth')->group(function () {
    Route::get('/',                                    [ChatController::class, 'index'])->name('index');
    Route::get('/messages/{userId}',                   [ChatController::class, 'messages'])->name('messages');
    Route::post('/send',                               [ChatController::class, 'send'])->name('send');
    Route::put('/message/{id}',                        [ChatController::class, 'editMessage'])->name('edit-message');
    Route::post('/typing/{userId}',                    [ChatController::class, 'typing'])->name('typing');
    Route::post('/react/{messageId}',                  [ChatController::class, 'react'])->name('react');
    Route::delete('/message/{id}',                     [ChatController::class, 'deleteMessage'])->name('delete-message');
    Route::post('/avatar',                             [ChatController::class, 'updateAvatar'])->name('avatar');
    Route::post('/pin/{chatWithId}',                   [ChatController::class, 'setPin'])->name('set-pin');
    Route::post('/pin/{chatWithId}/verify',            [ChatController::class, 'verifyPin'])->name('verify-pin');
    Route::delete('/pin/{chatWithId}',                 [ChatController::class, 'removePin'])->name('remove-pin');
    Route::post('/auto-delete/{chatWithId}',           [ChatController::class, 'updateAutoDelete'])->name('auto-delete');
});
// Routes that check session internally (no middleware needed)
Route::get('/chat/unread-total', [ChatController::class, 'unreadTotal'])->name('chat.unread-total');
Route::post('/chat/push-subscription', [ChatController::class, 'savePushSubscription'])->name('chat.push-subscription');

/*
|--------------------------------------------------------------------------
| Admin Routes — Protected by HiddenAuth (admin role)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware('hidden.auth:admin')->group(function () {
    Route::get('/dashboard',           [AdminController::class, 'dashboard'])->name('dashboard');

    // User Management
    Route::post('/users',              [AdminController::class, 'storeUser'])->name('users.store');
    Route::put('/users/{id}',          [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{id}',       [AdminController::class, 'deleteUser'])->name('users.delete');
    Route::post('/users/{id}/reset-password', [AdminController::class, 'resetPassword'])->name('users.reset-password');
    Route::post('/users/{id}/toggle',  [AdminController::class, 'toggleUser'])->name('users.toggle');

    // Settings
    Route::post('/settings',           [AdminController::class, 'updateSettings'])->name('settings.update');

    // Message Monitoring
    Route::get('/messages',            [AdminController::class, 'messages'])->name('messages');
    Route::delete('/messages/{id}',    [AdminController::class, 'deleteMessage'])->name('messages.delete');
});

/*
|--------------------------------------------------------------------------
| Broadcasting Auth (for private channels)
|--------------------------------------------------------------------------
*/
Route::post('/broadcasting/auth', function (\Illuminate\Http\Request $request) {
    $userId = session('hidden_user_id');
    if (!$userId) abort(403);

    $channelName = $request->channel_name;
    // Allow user to subscribe only to their own channel
    if (preg_match('/^private-chat\.(\d+)$/', $channelName, $m)) {
        if ((int)$m[1] === (int)$userId) {
            return response()->json(['auth' => 'ok']);
        }
    }
    abort(403);
})->middleware('web');