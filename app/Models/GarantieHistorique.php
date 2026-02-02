<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GarantieHistorique extends Model
{
    use HasFactory;

    protected $table = 'garantie_historiques';

    protected $fillable = [
        'garantie_id',
        'ancien_statut',
        'nouveau_statut',
        'utilisateur_id',
        'commentaire',
        'document_justificatif',
    ];

    /**
     * Relations
     */
    public function garantie(): BelongsTo
    {
        return $this->belongsTo(Garantie::class);
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }
}
