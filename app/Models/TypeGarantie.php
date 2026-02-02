<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeGarantie extends Model
{
    use HasFactory;

    protected $table = 'types_garanties';

    protected $fillable = [
        'code',
        'libelle',
        'type',
        'description',
        'decote_pourcentage',
        'ponderation_pourcentage',
        'actif',
    ];

    protected $casts = [
        'decote_pourcentage' => 'decimal:2',
        'ponderation_pourcentage' => 'decimal:2',
        'actif' => 'boolean',
    ];

    /**
     * Relations avec les garanties
     */
    public function garanties(): HasMany
    {
        return $this->hasMany(Garantie::class);
    }

    /**
     * Calculer la valeur réelle à partir de la valeur saisie
     */
    public function calculerValeurReelle(float $valeurSaisie): float
    {
        return $valeurSaisie * ($this->ponderation_pourcentage / 100);
    }
}
