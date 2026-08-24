<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteStatistic;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class SiteStatisticController extends Controller
{
    /**
     * Statistiques actives, triées par ordre d'affichage. Mêmes règles de
     * cache que PartnerController::index().
     */
    public function index(): JsonResponse
    {
        $statistiques = Cache::remember('site-statistics.active', now()->addMinutes(10), function () {
            return SiteStatistic::query()
                ->active()
                ->ordered()
                ->get()
                ->map(fn (SiteStatistic $s) => [
                    'id' => $s->id,
                    'label' => $s->label,
                    'value' => $s->value,
                    'icon' => $s->icon,
                ])
                ->values();
        });

        return response()->json([
            'success' => true,
            'data' => $statistiques,
        ]);
    }
}
