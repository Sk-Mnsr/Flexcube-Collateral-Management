<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Client;

class Garantie extends Model
{
    use HasFactory;

    protected $table = 'garanties';

    protected $fillable = [
        'reference_unique',
        'nom',
        'description',
        'emplacement',
        'type_garantie_id',
        'garant_id',
        'client_id',
        'valeur',
        'valeur_reelle',
        'statut',
        'date_creation',
        'date_expiration',
        'modifie_par',
        'date_modification',
    ];

    protected $casts = [
        'valeur' => 'decimal:2',
        'valeur_reelle' => 'decimal:2',
        'date_creation' => 'date',
        'date_expiration' => 'date',
        'date_modification' => 'datetime',
    ];

    /**
     * Génère une référence unique pour la garantie
     */
    public static function generateReference(): string
    {
        $year = date('Y');
        $prefix = 'GAR';
        
        $lastGarantie = self::where('reference_unique', 'like', "{$prefix}-{$year}-%")
            ->orderBy('reference_unique', 'desc')
            ->value('reference_unique');
        
        if ($lastGarantie) {
            $parts = explode('-', $lastGarantie);
            $lastNumber = isset($parts[2]) ? (int)$parts[2] : 0;
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        $formattedNumber = str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
        return "{$prefix}-{$year}-{$formattedNumber}";
    }

    /**
     * Relations
     */
    public function typeGarantie(): BelongsTo
    {
        return $this->belongsTo(TypeGarantie::class);
    }

    public function garant(): BelongsTo
    {
        return $this->belongsTo(Garant::class);
    }

    public function contratsPret(): BelongsToMany
    {
        return $this->belongsToMany(ContratPret::class, 'garantie_contrat_pret')
            ->withPivot('pourcentage_utilisation', 'montant_utilise', 'created_at')
            ->withTimestamps();
    }

    public function matriculesClients(): BelongsToMany
    {
        return $this->belongsToMany(MatriculeClient::class, 'garantie_matricule_client')
            ->withTimestamps();
    }

    public function documentations(): HasMany
    {
        return $this->hasMany(DocumentationGarantie::class);
    }

    public function historiques(): HasMany
    {
        return $this->hasMany(GarantieHistorique::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }


    /**
     * Calculer le montant total utilisé (somme des montants utilisés sur les contrats actifs)
     */
    public function calculerMontantUtilise(): float
    {
        return (float) DB::table('garantie_contrat_pret')
            ->join('contrats_prets', 'garantie_contrat_pret.contrat_pret_id', '=', 'contrats_prets.id')
            ->where('garantie_contrat_pret.garantie_id', $this->id)
            ->where('contrats_prets.statut', 'actif')
            ->sum('garantie_contrat_pret.montant_utilise');
    }

    /**
     * Calculer le montant restant
     */
    public function calculerMontantRestant(): float
    {
        return max(0, $this->valeur_reelle - $this->calculerMontantUtilise());
    }

    /**
     * Calculer le pourcentage d'utilisation
     */
    public function calculerPourcentageUtilisation(): float
    {
        if ($this->valeur_reelle == 0) {
            return 0;
        }
        return ($this->calculerMontantUtilise() / $this->valeur_reelle) * 100;
    }

    /**
     * Vérifier si la garantie est disponible pour un autre prêt
     */
    public function estDisponiblePourPret(): bool
    {
        return $this->calculerMontantRestant() > 0 && in_array($this->statut, ['normal', 'dation']);
    }

    /**
     * Workflow des statuts selon le type de garantie
     * 
     * Règles de gestion selon le document "Gestion des garanties par le Juridique" :
     * 
     * I. Garanties de type Nantissement et Gage :
     *    - Normal → Contentieux (Juridique uniquement)
     *    - Contentieux → Réalisation (Juridique uniquement)
     *    - Réalisation → Mutation à un tiers (Juridique uniquement)
     *    - Réalisation → Mutation au nom de Cofina (Juridique uniquement)
     *    - Réalisation → Main levée (Juridique uniquement)
     *    - Mutation au nom de Cofina → Vendu (Juridique uniquement)
     * 
     * II. Garanties de type Hypothécaire :
     *    - Normal → Contentieux (Juridique uniquement)
     *    - Normal → Dation (accessible depuis Normal)
     *    - Contentieux → Réalisation (Juridique uniquement)
     *    - Réalisation → Mutation à un tiers (Juridique uniquement)
     *    - Réalisation → Mutation au nom de Cofina (Juridique uniquement)
     *    - Réalisation → Main levée (Juridique uniquement)
     *    - Mutation au nom de Cofina → Vendu (Juridique uniquement)
     *    - Dation → Contentieux (si nécessaire après dation)
     */
    public function peutPasserA(string $nouveauStatut): bool
    {
        // Récupérer le type de garantie
        $typeGarantie = $this->typeGarantie;
        $typeCode = $typeGarantie ? strtolower($typeGarantie->code) : '';
        
        // Déterminer si c'est un type nantissement/gage ou hypothécaire
        $isNantissementGage = in_array($typeCode, ['nantissement', 'gage']);
        $isHypothecaire = strpos($typeCode, 'hypothec') !== false || strpos($typeCode, 'hypothécaire') !== false;

        // Workflow pour Nantissement et Gage
        if ($isNantissementGage) {
            $transitions = [
                'normal' => ['contentieux'],
                'contentieux' => ['realisation'],
                'realisation' => ['mutation_tiers', 'mutation_cofina', 'main_leve'],
                'mutation_tiers' => [], // État final
                'mutation_cofina' => ['vendu'],
                'vendu' => [], // État final
                'main_leve' => [], // État final
                'dation' => [], // Non applicable pour nantissement/gage
            ];
        }
        // Workflow pour Hypothécaire
        elseif ($isHypothecaire) {
            $transitions = [
                'normal' => ['contentieux', 'dation'],
                'contentieux' => ['realisation'],
                'realisation' => ['mutation_tiers', 'mutation_cofina', 'main_leve'],
                'mutation_tiers' => [], // État final
                'mutation_cofina' => ['vendu'],
                'vendu' => [], // État final
                'main_leve' => [], // État final
                'dation' => ['contentieux'], // Après dation, peut passer en contentieux si nécessaire
            ];
        }
        // Workflow par défaut (pour compatibilité avec d'autres types)
        else {
            $transitions = [
                'normal' => ['contentieux', 'dation'],
                'contentieux' => ['realisation'],
                'realisation' => ['mutation_tiers', 'mutation_cofina', 'main_leve'],
                'mutation_tiers' => [],
                'mutation_cofina' => ['vendu'],
                'vendu' => [],
                'main_leve' => [],
                'dation' => ['contentieux'],
            ];
        }

        return in_array($nouveauStatut, $transitions[$this->statut] ?? []);
    }

    /**
     * Vérifie si une transition nécessite le rôle juridique
     * 
     * Selon les règles de gestion, le Juridique est habilité à initier et valider
     * les changements de statut sensibles : Contentieux, Réalisation, Mutation, Main levée, Vente
     */
    public function transitionRequiertJuridique(string $nouveauStatut): bool
    {
        // Toutes les transitions sensibles nécessitent le rôle juridique :
        // - Normal → Contentieux
        // - Contentieux → Réalisation
        // - Réalisation → Mutation à un tiers
        // - Réalisation → Mutation au nom de Cofina
        // - Réalisation → Main levée
        // - Mutation au nom de Cofina → Vendu
        $transitionsJuridiques = [
            'contentieux',      // Normal → Contentieux
            'realisation',      // Contentieux → Réalisation
            'mutation_tiers',   // Réalisation → Mutation à un tiers
            'mutation_cofina',  // Réalisation → Mutation au nom de Cofina
            'main_leve',        // Réalisation → Main levée
            'vendu',            // Mutation au nom de Cofina → Vendu
        ];

        return in_array($nouveauStatut, $transitionsJuridiques);
    }

    /**
     * Changer le statut avec validation et historique
     */
    public function changerStatut(string $nouveauStatut, ?int $userId = null, ?string $commentaire = null, ?string $documentJustificatif = null): bool
    {
        if (!$this->peutPasserA($nouveauStatut)) {
            return false;
        }

        $ancienStatut = $this->statut;
        $this->statut = $nouveauStatut;
        if ($userId) {
            $this->modifie_par = $userId;
            $this->date_modification = now();
        }
        
        $saved = $this->save();

        // Enregistrer dans l'historique
        if ($saved && $userId) {
            \App\Models\GarantieHistorique::create([
                'garantie_id' => $this->id,
                'ancien_statut' => $ancienStatut,
                'nouveau_statut' => $nouveauStatut,
                'utilisateur_id' => $userId,
                'commentaire' => $commentaire,
                'document_justificatif' => $documentJustificatif,
            ]);
        }

        return $saved;
    }
}
