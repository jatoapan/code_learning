<?php

use Illuminate\Support\Facades\Broadcast;

// Canal privado por usuario — solo el propio usuario puede escuchar sus notificaciones
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (string) $user->id === (string) $id;
});

// Canal privado para el resultado del intento de código (IDE)
Broadcast::channel('attempts.{userId}', function ($user, $userId) {
    return (string) $user->id === (string) $userId;
});
