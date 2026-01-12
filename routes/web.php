<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Online Food API',
        'version' => '1.0',
        'endpoints' => [
            'api' => '/api',
        ]
    ]);
});

Route::get('/login', function() {
    return 'API Login: Use /api/login endpoint';
})->name('login');
