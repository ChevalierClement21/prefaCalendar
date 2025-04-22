<?php

namespace Database\Seeders;

use App\Models\Sector;
use Illuminate\Database\Seeder;

class SectorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sectors = [
            [
                'name' => 'Nord',
                'description' => 'Secteur Nord de la ville',
                'color' => '#FF5733',
            ],
            [
                'name' => 'Sud',
                'description' => 'Secteur Sud de la ville',
                'color' => '#33FF57',
            ],
            [
                'name' => 'Est',
                'description' => 'Secteur Est de la ville',
                'color' => '#3357FF',
            ],
            [
                'name' => 'Ouest',
                'description' => 'Secteur Ouest de la ville',
                'color' => '#F333FF',
            ],
            [
                'name' => 'Centre',
                'description' => 'Secteur Centre de la ville',
                'color' => '#FF33A1',
            ],
        ];

        foreach ($sectors as $sector) {
            Sector::create($sector);
        }
    }
}
