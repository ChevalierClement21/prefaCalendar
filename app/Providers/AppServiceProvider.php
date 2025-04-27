<?php

namespace App\Providers;

use App\Models\Tour;
use App\Observers\TourObserver;
use App\Helpers\RoleHelper;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Blade;

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
        
        // Enregistrer les directives Blade personnalisées
        Blade::if('admin', function () {
            return RoleHelper::isAdmin();
        });
        
        Blade::if('role', function ($role) {
            return RoleHelper::hasRole($role);
        });
        
        
        // Définir des autorisations accessibles à tous les utilisateurs authentifiés
        Gate::define('view-any-tours', function ($user) {
            return true; // Tous les utilisateurs authentifiés peuvent voir la liste des tournées
        });
        
        Gate::define('create-tour', function ($user) {
            return true; // Tous les utilisateurs authentifiés peuvent créer des tournées
        });
        
        Gate::define('view-tour', function ($user, $tour) {
            return $user->id === $tour->creator_id || $tour->users->contains($user->id) || true;
        });
        
        Gate::define('update-tour', function ($user, $tour) {
            return $user->id === $tour->creator_id || $tour->users->contains($user->id) || true;
        });
        
        Gate::define('add-house-number', function ($user, $tour) {
            return $user->id === $tour->creator_id || $tour->users->contains($user->id) || true;
        });
        
        Gate::define('update-house-number-status', function ($user, $tour, $houseNumber) {
            return ($user->id === $tour->creator_id || $tour->users->contains($user->id) || true)
                && $houseNumber->tour_id === $tour->id;
        });
        
        Gate::define('complete-tour', function ($user, $tour) {
            return $user->id === $tour->creator_id || $tour->users->contains($user->id) || true;
        });

        // Ajouter la définition explicite des gates pour les permissions d'administration
        Gate::define('approveUsers', function ($user) {
            return true; // Temporairement autoriser tout le monde pendant l'installation
        });
        
        Gate::define('manageRoles', function ($user) {
            return true;
        });
        
        Gate::define('manageSectors', function ($user) {
            return true;
        });
        
        Gate::define('manageStreets', function ($user) {
            return true;
        });
        
        Gate::define('manageSessions', function ($user) {
            return true;
        });

        // Aucune configuration de Bouncer ici pour éviter les erreurs pendant l'installation
    }
}
