<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificat;
use App\Models\Cours;
use App\Models\Inscription;
use App\Models\SiteStatistic;
use App\Models\VisitCounter;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class SiteStatisticController extends Controller
{
    /**
     * Statistiques actives, triées par ordre d'affichage. Mêmes règles de
     * cache que PartnerController::index() (voir son commentaire sur
     * ->toArray() avant mise en cache).
     *
     * Une statistique dont `value_source` est renseignée voit sa valeur
     * CALCULÉE à partir des données réelles ; la saisie manuelle est alors
     * ignorée. C'est ce qui garantit qu'un chiffre affiché sur la vitrine —
     * apprenants formés, visiteurs — correspond bien à la réalité.
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
                    'value' => $this->valeur($s),
                    'icon' => $s->icon,
                ])
                ->values()
                ->toArray();
        });

        return response()->json([
            'success' => true,
            'data' => $statistiques,
        ]);
    }

    /** Valeur calculée si une source est définie, sinon la valeur saisie. */
    private function valeur(SiteStatistic $statistique): string
    {
        $calculee = $this->calculer($statistique->value_source);

        return $calculee !== null
            ? $this->formater($calculee)
            : (string) $statistique->value;
    }

    /**
     * @return int|null null si aucune source calculée n'est définie.
     */
    private function calculer(?string $source): ?int
    {
        return match ($source) {
            // Apprenants distincts ayant au moins une inscription.
            SiteStatistic::SOURCE_APPRENANTS_INSCRITS =>
                Inscription::query()->distinct()->count('apprenant_id'),

            // « Formés » au sens strict : parcours mené à son terme. Un
            // apprenant inscrit mais n'ayant rien terminé ne compte pas.
            SiteStatistic::SOURCE_APPRENANTS_FORMES =>
                Inscription::query()->where('statut', 'termine')->distinct()->count('apprenant_id'),

            // Cumul réel des visites (VisitCounter agrège site_visits).
            SiteStatistic::SOURCE_VISITEURS =>
                (int) (VisitCounter::query()->orderByDesc('date')->value('total_visits') ?? 0),

            SiteStatistic::SOURCE_COURS_PUBLIES =>
                Cours::query()->where('statut', 'publie')->count(),

            SiteStatistic::SOURCE_CERTIFICATS =>
                Certificat::query()->count(),

            default => null,
        };
    }

    /** Séparateur de milliers, pour rester lisible au-delà du millier. */
    private function formater(int $nombre): string
    {
        return number_format($nombre, 0, ',', ' ');
    }
}
