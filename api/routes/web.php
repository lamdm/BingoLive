<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => 'BingoLive API',
        'docs' => '/api/health',
        'client' => env('FRONTEND_URL', 'http://localhost:5173'),
    ]);
});
