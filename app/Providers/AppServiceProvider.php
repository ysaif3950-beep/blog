<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Models\Post;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('create-post', function (User $user) {
            return $user->role === 'user';
        });

        Gate::define('admin-control', function (User $user) {
            return $user->role === 'admin';
        });
        Gate::define('update-post', function (User $user,post $post) {
            return $user->id==$post->user_id;
        });


        Paginator::useBootstrapFive();
    }
}
