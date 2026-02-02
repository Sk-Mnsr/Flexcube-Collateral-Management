<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\ContratPret;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'matricule',
        'nom',
        'prenom',
        'telephone',
    ];

    /**
     * Relation avec les contrats de prêts (via matricule_client)
     */
    public function contratsPrets(): HasMany
    {
        return $this->hasMany(ContratPret::class, 'matricule_client', 'matricule');
    }

    /**
     * Relation avec les garanties
     */
    public function garanties(): BelongsToMany
    {
        return $this->belongsToMany(Garantie::class, 'garantie_client')
            ->withTimestamps();
    }

    /**
     * Accessor pour le nom complet
     */
    public function getNomCompletAttribute(): string
    {
        return "{$this->prenom} {$this->nom}";
    }
}
