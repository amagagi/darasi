<?php
// app/Models/PaiementLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaiementLog extends Model
{
    protected $table = 'paiements_logs';
    
    protected $fillable = ['paiement_id', 'event_type', 'payload'];

    protected $casts = [
        'payload' => 'array',
    ];

    public function paiement()
    {
        return $this->belongsTo(Paiement::class);
    }
}