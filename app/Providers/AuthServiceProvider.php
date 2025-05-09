<?php

namespace App\Providers;

use App\Models\HouseNumber;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
// use Silber\Bouncer\Bouncer;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Définition du prédicat "admin"
        Gate::define('admin', function (User $user) {
            return true; // Temporairement autoriser tout le monde pendant l'installation
        });

        // Définition du prédicat "viewUsers"
        Gate::define('viewUsers', function (User $user) {
            return true; // Temporairement autoriser tout le monde pendant l'installation
        });
    }
}