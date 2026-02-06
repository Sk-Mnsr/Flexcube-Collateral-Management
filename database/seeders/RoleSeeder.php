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
                'nom' => 'SuperAdmin',
                'slug' => 'it',
                'description' => 'SuperAdmin (IT) - Administrateur avec tous les droits sur l\'application, y compris la configuration',
                'actif' => true,
            ],
            [
                'nom' => 'Admin',
                'slug' => 'admin',
                'description' => 'Admin (Admin crédit) - Administrateur avec tous les droits sauf la configuration',
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

