<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

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
        Schema::defaultStringLength(191);
        Vite::prefetch(event: 'hover');
        // So Inertia receives the same shape as raw models (no top-level "data" wrapper)
        JsonResource::withoutWrapping();

        Gate::before(function ($user, $ability) {
            if ($user instanceof User && $user->role === 'admin') {
                return true;
            }

            return null;
        });
    }
}
