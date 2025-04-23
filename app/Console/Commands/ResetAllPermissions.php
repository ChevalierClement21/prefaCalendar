<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Silber\Bouncer\BouncerFacade as Bouncer;
use Silber\Bouncer\Database\Ability;
use Silber\Bouncer\Database\Role;

class ResetAllPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:reset {--admin-email= : Email de l\'administrateur à configurer}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Réinitialise toutes les autorisations et configure l\'administrateur';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Réinitialisation des autorisations en cours...');

        // Vider les tables de permissions
        Bouncer::refresh();
        
        // Supprimer toutes les capacités existantes
        Ability::query()->delete();
        
        // Supprimer tous les rôles existants
        Role::query()->delete();
        
        $this->info('Tables de permissions nettoyées.');

        // Créer les rôles de base
        Bouncer::role()->create([
            'name' => 'admin',
            'title' => 'Administrateur',
        ]);

        Bouncer::role()->create([
            'name' => 'user',
            'title' => 'Utilisateur',
        ]);

        $this->info('Rôles créés: admin, user');

        // Créer les capacités
        $abilities = [
            'view-any-tours' => 'Voir toutes les tournées',
            'create-tour' => 'Créer une tournée',
            'view-tour' => 'Voir une tournée',
            'update-tour' => 'Modifier une tournée',
            'add-house-number' => 'Ajouter un numéro de maison',
            'update-house-number-status' => 'Modifier le statut d\'un numéro de maison',
            'complete-tour' => 'Terminer une tournée',
            'admin' => 'Accès administrateur',
            'viewUsers' => 'Voir les utilisateurs',
            'approveUsers' => 'Approuver les utilisateurs',
            'manageRoles' => 'Gérer les rôles',
            'manageSectors' => 'Gérer les secteurs',
            'manageStreets' => 'Gérer les rues',
        ];

        foreach ($abilities as $name => $title) {
            Bouncer::ability()->create([
                'name' => $name,
                'title' => $title,
            ]);
        }

        $this->info('Capacités créées.');

        // Attribuer les capacités au rôle utilisateur standard
        Bouncer::allow('user')->to(['view-any-tours', 'create-tour', 'view-tour', 'update-tour', 
            'add-house-number', 'update-house-number-status', 'complete-tour']);

        // Le rôle admin a automatiquement toutes les permissions grâce au BouncerServiceProvider
        Bouncer::allow('admin')->everything();

        $this->info('Capacités attribuées aux rôles.');

        // Configurer un administrateur si spécifié
        $adminEmail = $this->option('admin-email');
        if ($adminEmail) {
            $admin = User::where('email', $adminEmail)->first();
            
            if ($admin) {
                // Retirer tous les rôles existants
                Bouncer::sync($admin)->roles([]);
                
                // Attribuer le rôle admin
                Bouncer::assign('admin')->to($admin);
                
                // Approuver l'utilisateur
                if (!$admin->approved) {
                    $admin->approved = true;
                    $admin->save();
                }
                
                $this->info("L'utilisateur {$admin->firstname} {$admin->lastname} ({$admin->email}) a été configuré comme administrateur.");
            } else {
                $this->error("Aucun utilisateur trouvé avec l'email: {$adminEmail}");
            }
        }

        // Si aucun admin n'est spécifié, essayer de trouver un utilisateur et le configurer comme admin
        if (!$adminEmail) {
            $user = User::first();
            if ($user) {
                Bouncer::assign('admin')->to($user);
                
                if (!$user->approved) {
                    $user->approved = true;
                    $user->save();
                }
                
                $this->info("L'utilisateur {$user->firstname} {$user->lastname} ({$user->email}) a été configuré comme administrateur.");
            }
        }

        // Rafraîchir le cache de Bouncer
        Bouncer::refresh();

        $this->info('Réinitialisation des autorisations terminée avec succès!');
        return Command::SUCCESS;
    }
}