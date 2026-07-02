<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContenuJuridique extends Model
{
    protected $table = 'contenus_juridiques';

    protected $fillable = [
        'type',
        'titre',
        'contenu',
        'est_actif',
        'modifie_par',
        // 'date_modification',  ← SUPPRIME (Laravel gère automatiquement updated_at)
    ];

    protected $casts = [
        'est_actif' => 'boolean',
    ];

    public function modifiePar()
    {
        return $this->belongsTo(User::class, 'modifie_par');
    }
}