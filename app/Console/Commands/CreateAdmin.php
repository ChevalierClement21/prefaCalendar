<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Silber\Bouncer\BouncerFacade as Bouncer;

class CreateAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-admin {--firstname= : Prénom de l\'administrateur} {--lastname= : Nom de l\'administrateur} {--email= : Email de l\'administrateur} {--password= : Mot de passe de l\'administrateur}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Créer un compte administrateur initial';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $firstname = $this->option('firstname') ?: $this->ask('Prénom de l\'administrateur');
        $lastname = $this->option('lastname') ?: $this->ask('Nom de l\'administrateur');
        $email = $this->option('email') ?: $this->ask('Email de l\'administrateur');
        $password = $this->option('password') ?: $this->secret('Mot de passe de l\'administrateur');
        
        // Vérifier si l'email existe déjà
        $existingUser = User::where('email', $email)->first();
        
        if ($existingUser) {
            // Si l'utilisateur existe, mettre à jour ses informations et lui attribuer le rôle d'admin
            $existingUser->update([
                'firstname' => $firstname,
                'lastname' => $lastname,
                'password' => Hash::make($password),
                'approved' => true,
            ]);
            
            $user = $existingUser;
            $this->info('Utilisateur existant mis à jour avec succès.');
        } else {
            // Créer un nouvel utilisateur avec le rôle d'admin
            $user = User::create([
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $email,
                'password' => Hash::make($password),
                'approved' => true,
            ]);
            
            $this->info('Nouvel utilisateur administrateur créé avec succès.');
        }
        
        // Attribuer le rôle d'admin
        Bouncer::assign('admin')->to($user);
        
        $this->info("L'utilisateur {$email} a reçu le rôle d'administrateur.");
    }
}
