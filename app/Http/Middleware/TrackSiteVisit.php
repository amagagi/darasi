<?php

namespace App\Http\Middleware;

use App\Models\SiteVisit;
use App\Models\VisitCounter;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enregistre une visite de page publique. Attaché UNIQUEMENT à
 * POST /api/site-visits (pas un middleware global) : le frontend Flutter est
 * une SPA, Laravel ne voit jamais une navigation cliente vers /welcome ou
 * /plateformes — cet appel dédié, déclenché explicitement par le routeur
 * Flutter, en tient lieu.
 */
class TrackSiteVisit
{
    /** Fenêtre de dédoublonnage : une visite comptée par session/IP. */
    private const FENETRE_DEDOUBLONNAGE_MINUTES = 30;

    /**
     * UA de bots/crawlers/outils connus, plus une IA vide (les vrais
     * navigateurs envoient toujours un User-Agent).
     */
    private const MOTIF_BOTS = '/bot|crawler|spider|facebookexternalhit|whatsapp|curl|wget|python-requests|headless/i';

    public function handle(Request $request, Closure $next): Response
    {
        $userAgent = $request->userAgent();

        if (blank($userAgent) || preg_match(self::MOTIF_BOTS, $userAgent)) {
            return response()->noContent();
        }

        $donnees = $request->validate([
            'page_url' => ['required', 'string', 'max:255'],
            'session_id' => ['nullable', 'string', 'max:64'],
        ]);

        $ipHash = hash('sha256', $request->ip().config('app.key'));
        $sessionId = $donnees['session_id'] ?? null;

        // Dédoublonnage par session si le frontend en fournit une, sinon par
        // IP hachée — dans les deux cas sur la même fenêtre glissante.
        $colonne = $sessionId ? 'session_id' : 'ip_hash';
        $valeur = $sessionId ?? $ipHash;

        $estNouvelleSession = ! SiteVisit::where($colonne, $valeur)
            ->where('visited_at', '>=', now()->subMinutes(self::FENETRE_DEDOUBLONNAGE_MINUTES))
            ->exists();

        // Toujours journalisée (sert au classement des pages les plus vues),
        // que ce soit une nouvelle session ou non.
        SiteVisit::create([
            'visited_at' => now(),
            'ip_hash' => $ipHash,
            'user_agent' => $userAgent,
            'page_url' => $donnees['page_url'],
            'session_id' => $sessionId,
        ]);

        if ($estNouvelleSession) {
            $this->incrementerCompteurDuJour();
        }

        return $next($request);
    }

    private function incrementerCompteurDuJour(): void
    {
        $compteur = VisitCounter::where('date', today())->first();

        if ($compteur === null) {
            $dernierTotal = VisitCounter::orderByDesc('date')->value('total_visits') ?? 0;
            $compteur = VisitCounter::create([
                'date' => today(),
                'today_visits' => 0,
                'total_visits' => $dernierTotal,
            ]);
        }

        // UPDATE atomique en base, pas une lecture-modification-écriture PHP.
        $compteur->increment('today_visits');
        $compteur->increment('total_visits');
    }
}
