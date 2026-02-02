<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ContratPret extends Model
{
    use HasFactory;

    protected $table = 'contrats_prets';

    protected $fillable = [
        'numero_pret',
        'montant_accorde',
        'date_mise_en_place',
        'date_maturite',
        'statut',
        'code_gestionnaire',
        'code_agence',
        'matricule_client',
        'nom_client',
        'nature_juridique',
        'secteur_activite',
        'sync_flexcube_at',
    ];

    protected $casts = [
        'montant_accorde' => 'decimal:2',
        'date_mise_en_place' => 'date',
        'date_maturite' => 'date',
        'sync_flexcube_at' => 'datetime',
    ];

    /**
     * Relations
     */
    public function garanties(): BelongsToMany
    {
        return $this->belongsToMany(Garantie::class, 'garantie_contrat_pret')
            ->withPivot('pourcentage_utilisation', 'montant_utilise', 'created_at')
            ->withTimestamps();
    }

    /**
     * Vérifie si le contrat est actif
     */
    public function getEstActifAttribute(): bool
    {
        return $this->statut === 'actif';
    }
}
