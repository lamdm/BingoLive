<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Public room channel — anyone can listen to draws/chat
Broadcast::channel('room.{roomId}', function ($user, $roomId) {
    return true;
});
