<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\RoomController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json([
    'ok' => true,
    'product' => 'BingoLive',
    'stack' => 'laravel-api',
]));

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::get('/rooms', [RoomController::class, 'index']);
Route::get('/rooms/{room}', [RoomController::class, 'show']);
Route::get('/rooms/{room}/live', [RoomController::class, 'liveState']);
Route::get('/rooms/{room}/result', [RoomController::class, 'result']);
Route::get('/leaderboard', [AdminController::class, 'leaderboard']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::post('/rooms', [RoomController::class, 'store']);
    Route::post('/rooms/{room}/join', [RoomController::class, 'join']);

    Route::post('/rooms/{room}/start', [GameController::class, 'startRound']);
    Route::post('/rooms/{room}/draw', [GameController::class, 'drawNext']);
    Route::post('/rooms/{room}/pause', [GameController::class, 'pause']);
    Route::post('/rooms/{room}/resume', [GameController::class, 'resume']);
    Route::post('/rooms/{room}/end', [GameController::class, 'endRound']);
    Route::post('/rooms/{room}/stream', [GameController::class, 'toggleStream']);
    Route::post('/rooms/{room}/claim', [GameController::class, 'claim']);
    Route::post('/rooms/{room}/chat', [GameController::class, 'chat']);

    Route::get('/host/rooms', [AdminController::class, 'hostRooms']);
    Route::get('/admin/overview', [AdminController::class, 'overview']);
    Route::patch('/admin/users/{user}', [AdminController::class, 'updateUser']);
    Route::patch('/admin/rooms/{room}', [AdminController::class, 'updateRoom']);
});
