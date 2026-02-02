<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = [
            [
                'matricule' => 'CLI-2025-0001',
                'nom' => 'DIOP',
                'prenom' => 'Amadou',
                'telephone' => '+221 77 123 45 67',
            ],
            [
                'matricule' => 'CLI-2025-0002',
                'nom' => 'NDIAYE',
                'prenom' => 'Fatou',
                'telephone' => '+221 78 234 56 78',
            ],
            [
                'matricule' => 'CLI-2025-0003',
                'nom' => 'FALL',
                'prenom' => 'Moussa',
                'telephone' => '+221 76 345 67 89',
            ],
            [
                'matricule' => 'CLI-2025-0004',
                'nom' => 'SARR',
                'prenom' => 'Aissatou',
                'telephone' => '+221 77 456 78 90',
            ],
            [
                'matricule' => 'CLI-2025-0005',
                'nom' => 'BA',
                'prenom' => 'Ibrahima',
                'telephone' => '+221 78 567 89 01',
            ],
            [
                'matricule' => 'CLI-2025-0006',
                'nom' => 'TOURE',
                'prenom' => 'Mariama',
                'telephone' => '+221 76 678 90 12',
            ],
            [
                'matricule' => 'CLI-2025-0007',
                'nom' => 'SY',
                'prenom' => 'Ousmane',
                'telephone' => '+221 77 789 01 23',
            ],
            [
                'matricule' => 'CLI-2025-0008',
                'nom' => 'KANE',
                'prenom' => 'Awa',
                'telephone' => '+221 78 890 12 34',
            ],
            [
                'matricule' => 'CLI-2025-0009',
                'nom' => 'DIALLO',
                'prenom' => 'Cheikh',
                'telephone' => '+221 76 901 23 45',
            ],
            [
                'matricule' => 'CLI-2025-0010',
                'nom' => 'GUEYE',
                'prenom' => 'Khadidiatou',
                'telephone' => '+221 77 012 34 56',
            ],
        ];

        foreach ($clients as $clientData) {
            Client::updateOrCreate(
                ['matricule' => $clientData['matricule']],
                $clientData
            );
        }

        $this->command->info('✅ ' . count($clients) . ' clients créés avec succès !');
    }
}
