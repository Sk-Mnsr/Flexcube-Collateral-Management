<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentationGarantie extends Model
{
    use HasFactory;

    protected $table = 'documentations_garanties';

    protected $fillable = [
        'garantie_id',
        'type_documentation', // texte ou fichier
        'nom',
        'valeur',
        'description',
        'chemin_fichier',
    ];

    protected $casts = [
        'valeur' => 'decimal:2',
    ];

    /**
     * Relations
     */
    public function garantie(): BelongsTo
    {
        return $this->belongsTo(Garantie::class);
    }
}
