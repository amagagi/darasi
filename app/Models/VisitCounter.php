<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Agrégat quotidien des visites — une ligne par jour, `total_visits` étant le
 * cumul dénormalisé à la fin de ce jour. Recalculé/corrigé par la commande
 * `visits:aggregate` (App\Console\Commands\AggregateVisitCounters).
 */
class VisitCounter extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'today_visits',
        'total_visits',
    ];

    protected $casts = [
        'date' => 'date',
        'today_visits' => 'integer',
        'total_visits' => 'integer',
    ];
}
