<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\TypeGarantie;

class TypeGarantieSeeder extends Seeder
{
    /**
     * Seed les types de garanties selon la grille fournie dans la documentation
     */
    public function run(): void
    {
        $types = [
            [
                'code' => 'CAU-HYP',
                'libelle' => 'Caution Hypothécaire',
                'type' => 'Matérielle (réelle)',
                'description' => 'Hypothèque consentie par un tiers sur un bien immobilier lui appartenant à la garantie d\'une obligation personnelle contractée par une autre personne',
                'decote_pourcentage' => 30,
                'ponderation_pourcentage' => 70,
            ],
            [
                'code' => 'PAF-HYP',
                'libelle' => 'Promesse d\'Affectation Hypothécaire',
                'type' => 'Matérielle (réelle)',
                'description' => 'Engagement à hypothéquer le TF sur maison, terrain ou immeuble',
                'decote_pourcentage' => 30,
                'ponderation_pourcentage' => 70,
            ],
            [
                'code' => 'GAR-HYP',
                'libelle' => 'Hypothèque immobilière',
                'type' => 'Matérielle (réelle)',
                'description' => 'Titre foncier sur maison, terrain ou immeuble enregistré',
                'decote_pourcentage' => 20,
                'ponderation_pourcentage' => 80,
            ],
            [
                'code' => 'GAR-CAS',
                'libelle' => 'Caution solidaire (personnelle)',
                'type' => 'Personnelle',
                'description' => 'Caution d\'un tiers (souvent membre de la famille ou du groupe)',
                'decote_pourcentage' => 0,
                'ponderation_pourcentage' => 0,
            ],
            [
                'code' => 'GAR-DEP',
                'libelle' => 'Dépôt de garantie en numéraire',
                'type' => 'Financière',
                'description' => 'Somme bloquée sur un compte interne',
                'decote_pourcentage' => 0,
                'ponderation_pourcentage' => 100,
            ],
            [
                'code' => 'GAR-VEH',
                'libelle' => 'Gage de matériel roulant',
                'type' => 'Matérielle (réelle)',
                'description' => 'Véhicule personnel, taxi, moto',
                'decote_pourcentage' => 50,
                'ponderation_pourcentage' => 50,
            ],
            [
                'code' => 'GAR-MOB',
                'libelle' => 'Gage de matériel mobiliers',
                'type' => 'Matérielle (réelle)',
                'description' => 'Mobiliers',
                'decote_pourcentage' => 50,
                'ponderation_pourcentage' => 50,
            ],
            [
                'code' => 'GAR-MPF',
                'libelle' => 'Nantissement de matériels professionnels',
                'type' => 'Matérielle (réelle)',
                'description' => 'Matériels professionnels',
                'decote_pourcentage' => 0,
                'ponderation_pourcentage' => 0,
            ],
            [
                'code' => 'GAR-DAT',
                'libelle' => 'Nantissement (Transfert fiduciaire) DAT',
                'type' => 'Financière',
                'description' => 'DAT donné en garantie',
                'decote_pourcentage' => 0,
                'ponderation_pourcentage' => 100,
            ],
            [
                'code' => 'GAR-ASS',
                'libelle' => 'Assurance-crédit',
                'type' => 'Garantie dérivée',
                'description' => 'Couverture assurantielle en cas de perte de capacité',
                'decote_pourcentage' => 0, // Variable selon le cas
                'ponderation_pourcentage' => 0, // Variable selon le cas
            ],
            [
                'code' => 'GAR-FIN',
                'libelle' => 'Autre Garantie Financière',
                'type' => 'Financière',
                'description' => 'Garantie émise par une autre banque ou une institution pour garantir le prêt',
                'decote_pourcentage' => 10,
                'ponderation_pourcentage' => 90,
            ],
            [
                'code' => 'GAR-DIV',
                'libelle' => 'Autres garanties diverses',
                'type' => 'Divers',
                'description' => 'À usage spécifique, documenté et validé au cas par cas',
                'decote_pourcentage' => 0,
                'ponderation_pourcentage' => 0,
            ],
        ];

        foreach ($types as $type) {
            TypeGarantie::updateOrCreate(
                ['code' => $type['code']],
                array_merge($type, ['actif' => true])
            );
        }
    }
}
