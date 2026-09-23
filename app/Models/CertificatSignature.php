<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Signature figée sur un certificat : copie du signataire au moment de
 * l'émission, indépendante de ses modifications ultérieures.
 */
class CertificatSignature extends Model
{
    protected $table = 'certificat_signatures';

    protected $fillable = [
        'certificat_id',
        'signataire_id',
        'nom',
        'fonction',
        'image_signature',
        'ordre',
    ];

    protected $casts = [
        'ordre' => 'integer',
    ];

    public function certificat(): BelongsTo
    {
        return $this->belongsTo(Certificat::class);
    }

    public function signataire(): BelongsTo
    {
        return $this->belongsTo(Signataire::class);
    }
}
