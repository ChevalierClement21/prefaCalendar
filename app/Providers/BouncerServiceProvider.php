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
        // Dans Laravel 12, Bouncer est enregistré automatiquement par le package lui-même
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Détecter si on est en train d'exécuter composer
        $isComposerOperation = false;
        if (app()->runningInConsole()) {
            $composerCommandsToSkip = ['install', 'update', 'require', 'dump-autoload'];
            $currentCommand = request()->server('argv')[1] ?? '';
            
            foreach ($composerCommandsToSkip as $command) {
                if (strpos($currentCommand, $command) !== false) {
                    $isComposerOperation = true;
                    break;
                }
            }
        }
        
        // Ne pas exécuter le code Bouncer pendant les tests, l'installation via composer, 
        // ou les commandes artisan d'installation/migration
        if (app()->environment('testing') ||
            $isComposerOperation ||
            (app()->runningInConsole() && 
             (strpos(request()->server('argv')[1] ?? '', 'install') !== false ||
              strpos(request()->server('argv')[1] ?? '', 'migrate') !== false))) {
            return;
        }

        // Éviter d'exécuter le code Bouncer pendant l'installation ou si la base de données n'est pas configurée
        try {
            // Vérifier si la connexion à la base de données est disponible
            if (!$this->isDatabaseConfigured()) {
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
        } catch (\Exception $e) {
            // Ne rien faire en cas d'erreur de base de données pendant l'installation
            return;
        }
    }

    /**
     * Vérifie si la base de données est configurée et accessible.
     *
     * @return bool
     */
    private function isDatabaseConfigured(): bool
    {
        try {
            // Vérifier si les tables existent
            $tableExists = \Illuminate\Support\Facades\Schema::hasTable('abilities');
            return $tableExists;
        } catch (\Exception $e) {
            return false;
        }
    }
}
