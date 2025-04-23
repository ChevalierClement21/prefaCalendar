<?php

namespace App\Providers;

use App\Models\Tour;
use App\Observers\TourObserver;
use Illuminate\Support\ServiceProvider;
use Silber\Bouncer\BouncerFacade as Bouncer;
use Illuminate\Support\Facades\Gate;

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
        // Enregistrer l'observateur de tournée
        Tour::observe(TourObserver::class);
        
        // Définir des autorisations accessibles à tous les utilisateurs authentifiés
        Gate::define('view-any-tours', function ($user) {
            return true; // Tous les utilisateurs authentifiés peuvent voir la liste des tournées
        });
        
        Gate::define('create-tour', function ($user) {
            return true; // Tous les utilisateurs authentifiés peuvent créer des tournées
        });
        
        Gate::define('view-tour', function ($user, $tour) {
            return $user->id === $tour->creator_id || $tour->users->contains($user->id) || $user->isAn('admin');
        });
        
        Gate::define('update-tour', function ($user, $tour) {
            return $user->id === $tour->creator_id || $tour->users->contains($user->id) || $user->isAn('admin');
        });
        
        Gate::define('add-house-number', function ($user, $tour) {
            return $user->id === $tour->creator_id || $tour->users->contains($user->id) || $user->isAn('admin');
        });
        
        Gate::define('update-house-number-status', function ($user, $tour, $houseNumber) {
            return ($user->id === $tour->creator_id || $tour->users->contains($user->id) || $user->isAn('admin'))
                && $houseNumber->tour_id === $tour->id;
        });
        
        Gate::define('complete-tour', function ($user, $tour) {
            return $user->id === $tour->creator_id || $tour->users->contains($user->id) || $user->isAn('admin');
        });

        // Configuration des rôles et des autorisations avec Bouncer
        Bouncer::allow('admin')->to('viewUsers');
        Bouncer::allow('admin')->to('approveUsers');
        Bouncer::allow('admin')->to('manageRoles');
        Bouncer::allow('admin')->to('manageSectors');
        Bouncer::allow('admin')->to('manageStreets');
        Bouncer::allow('admin')->to('manageSessions');
        
        // Ajouter la définition explicite des gates pour les permissions d'administration
        Gate::define('approveUsers', function ($user) {
            return $user->isAn('admin');
        });
        
        Gate::define('manageRoles', function ($user) {
            return $user->isAn('admin');
        });
        
        Gate::define('manageSectors', function ($user) {
            return $user->isAn('admin');
        });
        
        Gate::define('manageStreets', function ($user) {
            return $user->isAn('admin');
        });
        
        Gate::define('manageSessions', function ($user) {
            return $user->isAn('admin');
        });
    }
}
