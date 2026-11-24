<?php

namespace App\Providers;

use App\Models\Publication;
use App\Models\User;
use App\Policies\PublicationPolicy;
use Illuminate\Support\Facades\Gate;
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
        // Enregistrement explicite de la Policy pour Publication
        Gate::policy(Publication::class, PublicationPolicy::class);

        // Définition du Gate 'gerer-categories' (réservé aux administrateurs)
        Gate::define('gerer-categories', function (User $user) {
            return $user->role === 'admin';
        });
    }
}
