<?php
// app/Models/Message.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'expediteur_id',
        'destinataire_id',
        'cours_id',
        'contenu',
        'est_lu',
        'lu_le'
    ];

    protected $casts = [
        'est_lu' => 'boolean',
        'lu_le' => 'datetime'
    ];

    public function expediteur()
    {
        return $this->belongsTo(User::class, 'expediteur_id');
    }

    public function destinataire()
    {
        return $this->belongsTo(User::class, 'destinataire_id');
    }

    public function cours()
    {
        return $this->belongsTo(Cours::class);
    }

    public function marquerCommeLu()
    {
        if (!$this->est_lu) {
            $this->update([
                'est_lu' => true,
                'lu_le' => now()
            ]);
        }
    }
}