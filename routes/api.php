<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\TagController;



Route::get('posts/search', [PostController::class, 'search']);
Route::apiResource('posts', PostController::class);
Route::apiResource('tags', TagController::class);

