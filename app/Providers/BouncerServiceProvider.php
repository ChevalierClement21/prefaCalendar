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
        // Configurer Bouncer pour accorder automatiquement toutes les permissions aux administrateurs
        Bouncer::ownedVia('roles');
        
        // Définir que les administrateurs peuvent tout faire sans vérifications explicites
        Bouncer::after(function ($user, $ability, $result, $arguments) {
            // Si l'utilisateur est un administrateur, autoriser toutes les actions sans exceptions
            if ($user->isAn('admin')) {
                return true;
            }
            
            return null; // Laisser Bouncer continuer avec ses vérifications normales
        });
        
        // Définir un joker (*) pour le rôle admin qui autorise tout
        Bouncer::allow('admin')->everything();
        
        // Configuration spécifique des capacités administratives que nous voulons que Bouncer reconnaisse
        $adminAbilities = [
            'viewUsers',
            'approveUsers',
            'manageRoles',
            'manageSectors',
            'manageStreets',
            'manageSessions',
            'manageTours',
            'viewAllTours',
            'editAllTours',
            'deleteAllTours',
            'viewStatistics',
            'exportData',
            'importData',
            // Ajoutez ici toutes les autres capacités que vous souhaitez reconnaître
        ];
        
        // Créer ces capacités dans la base de données si elles n'existent pas déjà
        foreach ($adminAbilities as $ability) {
            Bouncer::ability()->firstOrCreate(['name' => $ability]);
        }
    }
}
