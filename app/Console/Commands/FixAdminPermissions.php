<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Silber\Bouncer\BouncerFacade as Bouncer;

class FixAdminPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:fix-permissions {email? : L\'adresse email de l\'administrateur}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Répare les autorisations d\'un administrateur ou de tous les administrateurs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        if ($email) {
            $user = User::where('email', $email)->first();

            if (!$user) {
                $this->error("Aucun utilisateur avec l'adresse email {$email} n'a été trouvé.");
                return Command::FAILURE;
            }

            if (!$user->isAn('admin')) {
                $this->error("L'utilisateur {$user->firstname} {$user->lastname} n'est pas un administrateur.");
                return Command::FAILURE;
            }

            $this->fixPermissions($user);
            $this->info("Les autorisations de l'administrateur {$user->firstname} {$user->lastname} ont été réparées.");
            return Command::SUCCESS;
        } 
        
        // Fix permissions for all admins
        $admins = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();

        if ($admins->count() === 0) {
            $this->error("Aucun administrateur trouvé dans le système.");
            return Command::FAILURE;
        }

        foreach ($admins as $admin) {
            $this->fixPermissions($admin);
        }

        $this->info("Les autorisations de {$admins->count()} administrateurs ont été réparées.");
        return Command::SUCCESS;
    }

    private function fixPermissions(User $user)
    {
        // S'assurer que l'utilisateur a le rôle admin
        // Le rôle 'admin' accorde automatiquement toutes les permissions via BouncerServiceProvider
        Bouncer::assign('admin')->to($user);

        // S'assurer que l'utilisateur est approuvé
        if (!$user->approved) {
            $user->approved = true;
            $user->save();
        }
        
        // Rafraîchir les autorisations pour s'assurer que les changements sont pris en compte
        Bouncer::refresh();
    }
}