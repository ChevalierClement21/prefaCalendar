<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Silber\Bouncer\BouncerFacade as Bouncer;

class AssignUserRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:assign-role {--all : Attribue le rôle à tous les utilisateurs} {email? : L\'adresse email de l\'utilisateur spécifique}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Attribue le rôle d\'utilisateur standard à un ou tous les utilisateurs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $assignAll = $this->option('all');
        $email = $this->argument('email');

        if ($assignAll) {
            $users = User::all();
            $count = 0;

            foreach ($users as $user) {
                if (!$user->isA('user') && !$user->isAn('admin')) {
                    Bouncer::assign('user')->to($user);
                    $count++;
                }
            }

            $this->info("{$count} utilisateurs ont reçu le rôle 'user'.");
            return Command::SUCCESS;
        } 
        
        if ($email) {
            $user = User::where('email', $email)->first();

            if (!$user) {
                $this->error("Aucun utilisateur avec l'adresse email {$email} n'a été trouvé.");
                return Command::FAILURE;
            }

            if ($user->isA('user')) {
                $this->info("L'utilisateur {$user->firstname} {$user->lastname} a déjà le rôle 'user'.");
                return Command::SUCCESS;
            }

            Bouncer::assign('user')->to($user);
            $this->info("L'utilisateur {$user->firstname} {$user->lastname} ({$user->email}) a reçu le rôle 'user'.");
            return Command::SUCCESS;
        }

        $this->error("Veuillez spécifier une adresse email ou utiliser l'option --all.");
        return Command::FAILURE;
    }
}