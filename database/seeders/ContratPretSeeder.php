<?php

namespace Database\Seeders;

use App\Models\ContratPret;
use App\Models\Client;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ContratPretSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les clients existants
        $clients = Client::all();
        
        if ($clients->isEmpty()) {
            $this->command->warn('⚠️  Aucun client trouvé. Exécutez d\'abord ClientSeeder.');
            return;
        }

        $contratsPrets = [
            [
                'numero_pret' => 'PRT-2025-0001',
                'matricule_client' => 'CLI-2025-0001',
                'montant_accorde' => 5000000.00, // 5 millions FCFA
                'date_mise_en_place' => Carbon::now()->subMonths(3)->format('Y-m-d'),
                'date_maturite' => Carbon::now()->addMonths(9)->format('Y-m-d'),
                'statut' => 'actif',
                'code_gestionnaire' => 'GEST-001',
                'code_agence' => 'AG-DKR-001',
                'nom_client' => 'DIOP Amadou',
                'nature_juridique' => 'Particulier',
                'secteur_activite' => 'Commerce',
            ],
            [
                'numero_pret' => 'PRT-2025-0002',
                'matricule_client' => 'CLI-2025-0001',
                'montant_accorde' => 3000000.00, // 3 millions FCFA
                'date_mise_en_place' => Carbon::now()->subMonths(1)->format('Y-m-d'),
                'date_maturite' => Carbon::now()->addMonths(11)->format('Y-m-d'),
                'statut' => 'actif',
                'code_gestionnaire' => 'GEST-002',
                'code_agence' => 'AG-DKR-001',
                'nom_client' => 'DIOP Amadou',
                'nature_juridique' => 'Particulier',
                'secteur_activite' => 'Commerce',
            ],
            [
                'numero_pret' => 'PRT-2025-0003',
                'matricule_client' => 'CLI-2025-0002',
                'montant_accorde' => 7500000.00, // 7.5 millions FCFA
                'date_mise_en_place' => Carbon::now()->subMonths(6)->format('Y-m-d'),
                'date_maturite' => Carbon::now()->addMonths(6)->format('Y-m-d'),
                'statut' => 'actif',
                'code_gestionnaire' => 'GEST-001',
                'code_agence' => 'AG-DKR-002',
                'nom_client' => 'NDIAYE Fatou',
                'nature_juridique' => 'Particulier',
                'secteur_activite' => 'Services',
            ],
            [
                'numero_pret' => 'PRT-2025-0004',
                'matricule_client' => 'CLI-2025-0003',
                'montant_accorde' => 2000000.00, // 2 millions FCFA
                'date_mise_en_place' => Carbon::now()->subMonths(2)->format('Y-m-d'),
                'date_maturite' => Carbon::now()->addMonths(10)->format('Y-m-d'),
                'statut' => 'actif',
                'code_gestionnaire' => 'GEST-003',
                'code_agence' => 'AG-THS-001',
                'nom_client' => 'FALL Moussa',
                'nature_juridique' => 'Particulier',
                'secteur_activite' => 'Agriculture',
            ],
            [
                'numero_pret' => 'PRT-2025-0005',
                'matricule_client' => 'CLI-2025-0003',
                'montant_accorde' => 4500000.00, // 4.5 millions FCFA
                'date_mise_en_place' => Carbon::now()->subMonths(9)->format('Y-m-d'),
                'date_maturite' => Carbon::now()->addMonths(3)->format('Y-m-d'),
                'statut' => 'actif',
                'code_gestionnaire' => 'GEST-001',
                'code_agence' => 'AG-THS-001',
                'nom_client' => 'FALL Moussa',
                'nature_juridique' => 'Particulier',
                'secteur_activite' => 'Agriculture',
            ],
            [
                'numero_pret' => 'PRT-2025-0006',
                'matricule_client' => 'CLI-2025-0004',
                'montant_accorde' => 10000000.00, // 10 millions FCFA
                'date_mise_en_place' => Carbon::now()->subMonths(12)->format('Y-m-d'),
                'date_maturite' => Carbon::now()->addDays(90)->format('Y-m-d'),
                'statut' => 'actif',
                'code_gestionnaire' => 'GEST-002',
                'code_agence' => 'AG-DKR-003',
                'nom_client' => 'SARR Aissatou',
                'nature_juridique' => 'Entreprise',
                'secteur_activite' => 'Industrie',
            ],
            [
                'numero_pret' => 'PRT-2025-0007',
                'matricule_client' => 'CLI-2025-0005',
                'montant_accorde' => 6000000.00, // 6 millions FCFA
                'date_mise_en_place' => Carbon::now()->subMonths(4)->format('Y-m-d'),
                'date_maturite' => Carbon::now()->addMonths(8)->format('Y-m-d'),
                'statut' => 'actif',
                'code_gestionnaire' => 'GEST-003',
                'code_agence' => 'AG-STL-001',
                'nom_client' => 'BA Ibrahima',
                'nature_juridique' => 'Particulier',
                'secteur_activite' => 'Commerce',
            ],
            [
                'numero_pret' => 'PRT-2025-0008',
                'matricule_client' => 'CLI-2025-0006',
                'montant_accorde' => 3500000.00, // 3.5 millions FCFA
                'date_mise_en_place' => Carbon::now()->subMonths(8)->format('Y-m-d'),
                'date_maturite' => Carbon::now()->addMonths(4)->format('Y-m-d'),
                'statut' => 'actif',
                'code_gestionnaire' => 'GEST-001',
                'code_agence' => 'AG-DKR-001',
                'nom_client' => 'TOURE Mariama',
                'nature_juridique' => 'Particulier',
                'secteur_activite' => 'Services',
            ],
            [
                'numero_pret' => 'PRT-2025-0009',
                'matricule_client' => 'CLI-2025-0007',
                'montant_accorde' => 8000000.00, // 8 millions FCFA
                'date_mise_en_place' => Carbon::now()->subMonths(15)->format('Y-m-d'),
                'date_maturite' => Carbon::now()->subDays(30)->format('Y-m-d'), // Expiré
                'statut' => 'soldé',
                'code_gestionnaire' => 'GEST-002',
                'code_agence' => 'AG-DKR-002',
                'nom_client' => 'SY Ousmane',
                'nature_juridique' => 'Particulier',
                'secteur_activite' => 'Commerce',
            ],
            [
                'numero_pret' => 'PRT-2025-0010',
                'matricule_client' => 'CLI-2025-0008',
                'montant_accorde' => 2500000.00, // 2.5 millions FCFA
                'date_mise_en_place' => Carbon::now()->subMonths(5)->format('Y-m-d'),
                'date_maturite' => Carbon::now()->addMonths(7)->format('Y-m-d'),
                'statut' => 'actif',
                'code_gestionnaire' => 'GEST-003',
                'code_agence' => 'AG-THS-001',
                'nom_client' => 'KANE Awa',
                'nature_juridique' => 'Particulier',
                'secteur_activite' => 'Services',
            ],
            [
                'numero_pret' => 'PRT-2025-0011',
                'matricule_client' => 'CLI-2025-0009',
                'montant_accorde' => 5500000.00, // 5.5 millions FCFA
                'date_mise_en_place' => Carbon::now()->subMonths(7)->format('Y-m-d'),
                'date_maturite' => Carbon::now()->addMonths(5)->format('Y-m-d'),
                'statut' => 'actif',
                'code_gestionnaire' => 'GEST-001',
                'code_agence' => 'AG-STL-001',
                'nom_client' => 'DIALLO Cheikh',
                'nature_juridique' => 'Particulier',
                'secteur_activite' => 'Commerce',
            ],
            [
                'numero_pret' => 'PRT-2025-0012',
                'matricule_client' => 'CLI-2025-0010',
                'montant_accorde' => 4000000.00, // 4 millions FCFA
                'date_mise_en_place' => Carbon::now()->subMonths(10)->format('Y-m-d'),
                'date_maturite' => Carbon::now()->addMonths(2)->format('Y-m-d'),
                'statut' => 'actif',
                'code_gestionnaire' => 'GEST-002',
                'code_agence' => 'AG-DKR-003',
                'nom_client' => 'GUEYE Khadidiatou',
                'nature_juridique' => 'Entreprise',
                'secteur_activite' => 'Services',
            ],
            [
                'numero_pret' => 'PRT-2025-0013',
                'matricule_client' => 'CLI-2025-0002',
                'montant_accorde' => 12000000.00, // 12 millions FCFA
                'date_mise_en_place' => Carbon::now()->subDays(30)->format('Y-m-d'),
                'date_maturite' => Carbon::now()->addMonths(12)->format('Y-m-d'),
                'statut' => 'actif',
                'code_gestionnaire' => 'GEST-001',
                'code_agence' => 'AG-DKR-001',
                'nom_client' => 'NDIAYE Fatou',
                'nature_juridique' => 'Particulier',
                'secteur_activite' => 'Commerce',
            ],
            [
                'numero_pret' => 'PRT-2025-0014',
                'matricule_client' => 'CLI-2025-0004',
                'montant_accorde' => 9000000.00, // 9 millions FCFA
                'date_mise_en_place' => Carbon::now()->subMonths(18)->format('Y-m-d'),
                'date_maturite' => Carbon::now()->subDays(60)->format('Y-m-d'), // Expiré
                'statut' => 'annulé',
                'code_gestionnaire' => 'GEST-002',
                'code_agence' => 'AG-DKR-002',
                'nom_client' => 'SARR Aissatou',
                'nature_juridique' => 'Entreprise',
                'secteur_activite' => 'Industrie',
            ],
            [
                'numero_pret' => 'PRT-2025-0015',
                'matricule_client' => 'CLI-2025-0005',
                'montant_accorde' => 6500000.00, // 6.5 millions FCFA
                'date_mise_en_place' => Carbon::now()->subMonths(2)->format('Y-m-d'),
                'date_maturite' => Carbon::now()->addMonths(10)->format('Y-m-d'),
                'statut' => 'actif',
                'code_gestionnaire' => 'GEST-003',
                'code_agence' => 'AG-STL-001',
                'nom_client' => 'BA Ibrahima',
                'nature_juridique' => 'Particulier',
                'secteur_activite' => 'Commerce',
            ],
        ];

        $created = 0;
        $skipped = 0;

        foreach ($contratsPrets as $contratData) {
            // Vérifier que le client existe
            $client = Client::where('matricule', $contratData['matricule_client'])->first();
            
            if (!$client) {
                $this->command->warn("⚠️  Client non trouvé pour le matricule: {$contratData['matricule_client']}. Contrat ignoré.");
                $skipped++;
                continue;
            }

            // Si nom_client n'est pas défini, utiliser les infos du client
            if (!isset($contratData['nom_client']) || empty($contratData['nom_client'])) {
                $contratData['nom_client'] = "{$client->prenom} {$client->nom}";
            }

            $contrat = ContratPret::updateOrCreate(
                ['numero_pret' => $contratData['numero_pret']],
                $contratData
            );

            if ($contrat->wasRecentlyCreated) {
                $created++;
            }
        }

        $this->command->info("✅ {$created} contrats de prêts créés avec succès !");
        if ($skipped > 0) {
            $this->command->warn("⚠️  {$skipped} contrats ignorés (clients introuvables).");
        }
    }
}
