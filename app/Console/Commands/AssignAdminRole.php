<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Silber\Bouncer\BouncerFacade as Bouncer;

class AssignAdminRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:make-admin {email : The email of the user to make admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign the admin role to a user by email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("Aucun utilisateur trouvé avec l'email: {$email}");
            return 1;
        }
        
        // Assigner le rôle admin
        Bouncer::assign('admin')->to($user);
        
        // Permettre les capacités spécifiques
        Bouncer::allow($user)->to('access-admin-panel');
        Bouncer::allow($user)->to('manage-sectors');
        Bouncer::allow($user)->to('manage-streets');
        
        $this->info("L'utilisateur {$user->firstname} {$user->lastname} ({$user->email}) est maintenant administrateur.");
        
        return 0;
    }
}
