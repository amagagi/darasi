<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContenuSite;
use App\Models\VisitCounter;
use Illuminate\Http\JsonResponse;

class SiteVisitController extends Controller
{
    /**
     * L'enregistrement effectif est fait par le middleware TrackSiteVisit
     * (voir routes/api.php) avant que cette méthode ne soit atteinte.
     */
    public function store(): JsonResponse
    {
        return response()->json(['success' => true]);
    }

    /**
     * Lecture publique légère pour le footer : total de visites + réglage
     * d'affichage (texte, activé/désactivé) stocké dans contenus_site sous la
     * clé ContenuSite::CLE_COMPTEUR_VISITES.
     */
    public function summary(): JsonResponse
    {
        $reglage = ContenuSite::query()
            ->where('cle', ContenuSite::CLE_COMPTEUR_VISITES)
            ->first();

        // Pas de ligne de réglage = compteur affiché par défaut, avec un
        // texte générique : rien à configurer pour l'obtenir.
        $actif = $reglage?->est_actif ?? true;
        $texte = $reglage?->contenu ?: '{n} visites';

        $total = VisitCounter::query()->orderByDesc('date')->value('total_visits') ?? 0;

        return response()->json([
            'success' => true,
            'data' => [
                'enabled' => $actif,
                'total_visits' => $total,
                'text_template' => $texte,
            ],
        ]);
    }
}
