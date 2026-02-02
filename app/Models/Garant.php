<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Garant extends Model
{
    use HasFactory;

    protected $fillable = [
        'civilite',
        'nom',
        'prenom',
        'adresse',
        'date_naissance',
        'lieu_naissance',
        'nationalite',
        'activite',
        'adresse_activite',
        'type_piece_identite',
        'numero_piece_identite',
        'fichier_piece_identite',
        'date_delivrance_piece_identite',
        'date_expiration_piece_identite',
        'telephone',
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'date_delivrance_piece_identite' => 'date',
        'date_expiration_piece_identite' => 'date',
    ];

    /**
     * Relations avec les garanties
     */
    public function garanties(): HasMany
    {
        return $this->hasMany(Garantie::class);
    }

    /**
     * Accessor pour le nom complet
     */
    public function getNomCompletAttribute(): string
    {
        return "{$this->prenom} {$this->nom}";
    }

    /**
     * Vérifie si un garant peut être utilisé (pas de garantie non terminée avec mêmes nom, prénom et date de naissance)
     */
    public static function canCreate(array $data): bool
    {
        return !self::where('nom', $data['nom'])
            ->where('prenom', $data['prenom'])
            ->where('date_naissance', $data['date_naissance'])
            ->whereHas('garanties', function ($query) {
                $query->whereNotIn('statut', ['completed', 'vendu', 'main_leve']);
            })
            ->exists();
    }
}
