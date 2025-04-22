<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Silber\Bouncer\BouncerFacade as Bouncer;

class MakeUserAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:make-admin {email : L\'adresse email de l\'utilisateur}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Attribue le rôle d\'administrateur à un utilisateur';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("Aucun utilisateur avec l'adresse email {$email} n'a été trouvé.");
            return Command::FAILURE;
        }

        // Assigner le rôle d'administrateur
        Bouncer::assign('admin')->to($user);
        
        // Approuver l'utilisateur si ce n'est pas déjà fait
        if (!$user->approved) {
            $user->approved = true;
            $user->save();
        }

        $this->info("L'utilisateur {$user->firstname} {$user->lastname} ({$user->email}) est maintenant administrateur.");
        return Command::SUCCESS;
    }
}