<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Silber\Bouncer\BouncerFacade as Bouncer;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer un administrateur
        $admin = User::create([
            'firstname' => 'Admin',
            'lastname' => 'Système',
            'email' => 'admin@exemple.com',
            'password' => Hash::make('password'),
            'approved' => true,
            'email_verified_at' => now(),
        ]);
        
        // Assigner le rôle admin
        Bouncer::assign('admin')->to($admin);
        
        // Créer des utilisateurs normaux approuvés
        $users = [
            [
                'firstname' => 'Jean',
                'lastname' => 'Dupont',
                'email' => 'jean.dupont@exemple.com',
                'password' => Hash::make('password'),
                'approved' => true,
            ],
            [
                'firstname' => 'Marie',
                'lastname' => 'Martin',
                'email' => 'marie.martin@exemple.com',
                'password' => Hash::make('password'),
                'approved' => true,
            ],
            [
                'firstname' => 'Pierre',
                'lastname' => 'Lefebvre',
                'email' => 'pierre.lefebvre@exemple.com',
                'password' => Hash::make('password'),
                'approved' => true,
            ],
        ];
        
        foreach ($users as $userData) {
            User::create($userData);
        }
        
        // Créer des utilisateurs en attente d'approbation
        $pendingUsers = [
            [
                'firstname' => 'Sophie',
                'lastname' => 'Dubois',
                'email' => 'sophie.dubois@exemple.com',
                'password' => Hash::make('password'),
                'approved' => false,
            ],
            [
                'firstname' => 'Thomas',
                'lastname' => 'Bernard',
                'email' => 'thomas.bernard@exemple.com',
                'password' => Hash::make('password'),
                'approved' => false,
            ],
        ];
        
        foreach ($pendingUsers as $userData) {
            User::create($userData);
        }
    }
}
