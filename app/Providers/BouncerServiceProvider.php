<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Silber\Bouncer\BouncerFacade as Bouncer;

class BouncerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Ne pas exécuter le code Bouncer pendant les tests
        if (app()->environment('testing')) {
            return;
        }

        // Définition des rôles et des capacités
        Bouncer::allow('admin')->to('access-admin-panel');
        
        // Seuls les administrateurs peuvent gérer les secteurs et les rues
        Bouncer::allow('admin')->to('manage-sectors');
        Bouncer::allow('admin')->to('manage-streets');
        
        // Les utilisateurs normaux ne peuvent pas accéder aux fonctionnalités d'administration
        Bouncer::forbid('user')->to('access-admin-panel');
        Bouncer::forbid('user')->to('manage-sectors');
        Bouncer::forbid('user')->to('manage-streets');
    }
}
