<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Une ligne par vue de page publique (voir App\Http\Middleware\TrackSiteVisit).
 * Alimente le compteur (via VisitCounter) et le classement des pages les plus
 * consultées.
 */
class SiteVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'visited_at',
        'ip_hash',
        'user_agent',
        'page_url',
        'session_id',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
    ];
}
