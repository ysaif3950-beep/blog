<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
// return view('home');
// });

Route::middleware('auth')->group(function () {

    Route::get('/', [PostController::class, 'home'])->name('dashboard');
    Route::get('posts/search', [PostController::class, 'search']);
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
    Route::resource('posts', PostController::class);

    Route::get('users/profile/edit', [UserController::class, 'editProfile'])->name('users.profile.edit');
    Route::put('users/profile', [UserController::class, 'updateProfile'])->name('users.profile.update');
    Route::get('users/profile', [UserController::class, 'profile'])->name('users.profile');
    Route::delete('users/profile', [UserController::class, 'destroySelf'])->name('users.profile.destroy');
    Route::resource('users', UserController::class);
    Route::get('users/{user}/posts', [UserController::class, 'posts'])->name('users.posts');
    Route::resource('tags', TagController::class);
});

Auth::routes();

Route::get('/home', [PostController::class, 'home'])->name('home');
