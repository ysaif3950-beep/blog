<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\Usercontroller ;
use App\Http\Controllers\Api\Authcontroller ;

Route::post('/login', [AuthController::class, 'login']);

Route::get('posts/search', [PostController::class, 'search']);
Route::apiResource('posts', PostController::class);
Route::apiResource('tags', TagController::class);
Route::apiResource('users', Usercontroller::class);


