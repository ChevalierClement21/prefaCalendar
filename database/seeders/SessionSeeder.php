<?php

namespace Database\Seeders;

use App\Models\Session;
use Illuminate\Database\Seeder;

class SessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Création d'une session par défaut qui sera active
        $defaultSession = Session::create([
            'name' => 'Session par défaut',
            'year' => date('Y'),  // Année actuelle
            'is_active' => true,
        ]);

        $this->command->info('Session par défaut créée et activée !');
    }
}
