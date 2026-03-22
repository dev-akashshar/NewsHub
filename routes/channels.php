<?php

use Illuminate\Support\Facades\Broadcast;

// Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });

Broadcast::channel('chat.{userId}', function ($user, $userId) {
    // $user here is the authenticated user via session
    return (int) session('hidden_user_id') === (int) $userId;
});