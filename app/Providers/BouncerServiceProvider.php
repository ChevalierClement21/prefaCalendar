<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// use Silber\Bouncer\BouncerFacade as Bouncer;

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
        // Tout est temporairement désactivé pour permettre l'installation
    }
}
