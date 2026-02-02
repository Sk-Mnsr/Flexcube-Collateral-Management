<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'nom' => 'IT (Admin)',
                'slug' => 'it',
                'description' => 'IT - Administrateur avec tous les droits sur l\'application',
                'actif' => true,
            ],
            [
                'nom' => 'Analyste Risque',
                'slug' => 'analyste-risque',
                'description' => 'Analyste Risque - Accès en lecture et édition pour l\'analyse des risques',
                'actif' => true,
            ],
            [
                'nom' => 'Chargé d\'Affaires',
                'slug' => 'charge-affaires',
                'description' => 'Chargé d\'Affaires - Accès en lecture seule',
                'actif' => true,
            ],
            [
                'nom' => 'Juridique',
                'slug' => 'juridique',
                'description' => 'Juridique - Gestion des statuts des garanties et contentieux',
                'actif' => true,
            ],

        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['slug' => $roleData['slug']],
                [
                    'nom' => $roleData['nom'],
                    'slug' => $roleData['slug'],
                    'description' => $roleData['description'],
                    'actif' => $roleData['actif'],
                ]
            );
        }
    }
}

