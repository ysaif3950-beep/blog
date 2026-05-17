<?php

use App\Http\Controllers\Api\V1\AuthController;
/*
|--------------------------------------------------------------------------
| API Version 1 Routes
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api/v1
| Example: /api/v1/auth/login
|
*/

use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\TagController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Middleware\EnsureAccessTokenSessionIsActive;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->middleware('throttle:auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    });

    Route::middleware([EnsureAccessTokenSessionIsActive::class, 'auth:sanctum', 'throttle:authenticated'])->group(function () {

        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/user', [AuthController::class, 'user']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
        });

        Route::get('posts/search', [PostController::class, 'search'])
            ->middleware('throttle:heavy');

        Route::apiResource('posts', PostController::class);

        Route::apiResource('tags', TagController::class);

        Route::apiResource('users', UserController::class);
    });
});
