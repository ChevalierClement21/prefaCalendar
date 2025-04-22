<?php

namespace Database\Seeders;

use App\Models\Sector;
use App\Models\Street;
use Illuminate\Database\Seeder;

class StreetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $streets = [
            // Secteur Nord
            [
                'sector_name' => 'Nord',
                'streets' => [
                    [
                        'name' => 'Rue des Lilas',
                        'postal_code' => '75001',
                        'city' => 'Paris',
                        'notes' => 'Accès difficile pour les camions',
                    ],
                    [
                        'name' => 'Avenue des Roses',
                        'postal_code' => '75001',
                        'city' => 'Paris',
                        'notes' => 'Présence de bornes à incendie tous les 100m',
                    ],
                    [
                        'name' => 'Boulevard du Nord',
                        'postal_code' => '75001',
                        'city' => 'Paris',
                        'notes' => 'Voie large, accès facile',
                    ],
                ],
            ],
            // Secteur Sud
            [
                'sector_name' => 'Sud',
                'streets' => [
                    [
                        'name' => 'Rue du Midi',
                        'postal_code' => '75005',
                        'city' => 'Paris',
                        'notes' => 'Rue étroite, attention aux stationnements',
                    ],
                    [
                        'name' => 'Avenue du Soleil',
                        'postal_code' => '75005',
                        'city' => 'Paris',
                        'notes' => 'Large voie, bonne accessibilité',
                    ],
                    [
                        'name' => 'Impasse des Oliviers',
                        'postal_code' => '75005',
                        'city' => 'Paris',
                        'notes' => 'Impasse étroite, manœuvre difficile',
                    ],
                ],
            ],
            // Secteur Est
            [
                'sector_name' => 'Est',
                'streets' => [
                    [
                        'name' => 'Rue de l\'Aurore',
                        'postal_code' => '75011',
                        'city' => 'Paris',
                        'notes' => 'Présence d\'écoles, attention aux horaires',
                    ],
                    [
                        'name' => 'Boulevard de l\'Est',
                        'postal_code' => '75011',
                        'city' => 'Paris',
                        'notes' => 'Accès facile, large voie',
                    ],
                    [
                        'name' => 'Avenue des Cerisiers',
                        'postal_code' => '75011',
                        'city' => 'Paris',
                        'notes' => 'Zone résidentielle, attention au stationnement',
                    ],
                ],
            ],
            // Secteur Ouest
            [
                'sector_name' => 'Ouest',
                'streets' => [
                    [
                        'name' => 'Rue du Couchant',
                        'postal_code' => '75016',
                        'city' => 'Paris',
                        'notes' => 'Zone très résidentielle',
                    ],
                    [
                        'name' => 'Avenue de l\'Océan',
                        'postal_code' => '75016',
                        'city' => 'Paris',
                        'notes' => 'Large voie, bonne accessibilité',
                    ],
                    [
                        'name' => 'Boulevard des Pins',
                        'postal_code' => '75016',
                        'city' => 'Paris',
                        'notes' => 'Présence de nombreux commerces',
                    ],
                ],
            ],
            // Secteur Centre
            [
                'sector_name' => 'Centre',
                'streets' => [
                    [
                        'name' => 'Place Centrale',
                        'postal_code' => '75002',
                        'city' => 'Paris',
                        'notes' => 'Zone piétonne, accès restreint',
                    ],
                    [
                        'name' => 'Rue du Commerce',
                        'postal_code' => '75002',
                        'city' => 'Paris',
                        'notes' => 'Nombreux commerces, attention aux heures d\'affluence',
                    ],
                    [
                        'name' => 'Avenue des Arts',
                        'postal_code' => '75002',
                        'city' => 'Paris',
                        'notes' => 'Présence de musées et galeries',
                    ],
                ],
            ],
        ];

        foreach ($streets as $sectorData) {
            $sector = Sector::where('name', $sectorData['sector_name'])->first();
            
            if ($sector) {
                foreach ($sectorData['streets'] as $streetData) {
                    Street::create([
                        'sector_id' => $sector->id,
                        'name' => $streetData['name'],
                        'postal_code' => $streetData['postal_code'],
                        'city' => $streetData['city'],
                        'notes' => $streetData['notes'],
                    ]);
                }
            }
        }
    }
}
