<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

//Route::get('/', function () {
   // return view('home');
//});
Route::get('/',[PostController::class,'home']);
/*
Route::get('posts',[PostController::class,'index']);
Route::get('posts/create',[PostController::class,'create']);
Route::get('posts/search',[PostController::class,'search']);
Route::get('posts/{id}',[PostController::class,'show']);
Route::get('posts/{id}/edit',[PostController::class,'edit']);
Route::post('posts',[PostController::class,'store']);
Route::delete('posts/{id}',[PostController::class,'destroy']);
Route::put('posts/{id}',[PostController::class,'update']);
*/
Route::resource('posts',PostController::class);

Route::resource('users',UserController::class);
Route::get('users/{id}/posts', [UserController::class, 'posts'])->name('users.posts');
Route::resource('tags',TagController::class);


