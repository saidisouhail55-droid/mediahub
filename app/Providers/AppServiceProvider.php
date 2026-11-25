<?php

namespace App\Providers;

use App\Models\Publication;
use App\Models\User;
use App\Policies\PublicationPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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

        RateLimiter::for('login', function (Request $request) {
            $email = Str::transliterate(Str::lower($request->string('email')->toString()));

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });
    }
}
