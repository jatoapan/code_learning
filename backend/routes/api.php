<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', function () {
        return response()->json(['status' => 'ok']);
    });

    // Módulo 4.1 Autenticación y Perfil
    Route::post('/users', [\App\Http\Controllers\Api\V1\AuthController::class, 'register']);
    Route::post('/sessions', [\App\Http\Controllers\Api\V1\AuthController::class, 'login']);
    
    // Rutas protegidas
    Route::middleware('auth:sanctum')->group(function () {
        Route::delete('/sessions/current', [\App\Http\Controllers\Api\V1\AuthController::class, 'logout']);
        Route::get('/user', [\App\Http\Controllers\Api\V1\UserController::class, 'me']);
        Route::put('/user', [\App\Http\Controllers\Api\V1\UserController::class, 'update']);
        Route::delete('/users/me', [\App\Http\Controllers\Api\V1\UserController::class, 'deactivate']);
        
        // Cursos
        Route::get('/courses', [\App\Http\Controllers\Api\V1\CourseController::class, 'index']);
        Route::get('/courses/{id}', [\App\Http\Controllers\Api\V1\CourseController::class, 'show']);
    });
});
