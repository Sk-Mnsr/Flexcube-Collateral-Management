<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MatriculeClient extends Model
{
    use HasFactory;

    protected $table = 'matricules_clients';

    protected $fillable = [
        'matricule',
        'nom',
        'nature_juridique', // Entreprise / Particulier
        'secteur_activite',
        'sync_flexcube_at',
    ];

    protected $casts = [
        'sync_flexcube_at' => 'datetime',
    ];

    /**
     * Relations
     */
    public function garanties(): BelongsToMany
    {
        return $this->belongsToMany(Garantie::class, 'garantie_matricule_client')
            ->withTimestamps();
    }
}
