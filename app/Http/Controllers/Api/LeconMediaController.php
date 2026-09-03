<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inscription;
use App\Models\Lecon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Diffusion sécurisée des médias de leçon (PDF et vidéo).
 *
 * Deux étages :
 *
 *  1. `refresh()` — protégé par Sanctum. Vérifie l'inscription au cours puis
 *     délivre une URL signée valable 30 minutes.
 *  2. `stream()`  — protégé par la signature (middleware `signed`), sans
 *     Sanctum. C'est la signature qui fait autorité ici : sur le Web, la balise
 *     <video> et le moteur PDF chargent l'URL eux-mêmes et ne peuvent pas
 *     joindre d'en-tête Authorization.
 *
 * Le fichier est servi depuis le disque privé, en `Content-Disposition: inline`
 * pour empêcher le téléchargement forcé, et avec support des requêtes `Range`
 * (réponses 206) pour permettre l'avance rapide sans télécharger la vidéo
 * entière.
 *
 * Routes :
 *   GET /api/lecons/{id}/media            (auth:sanctum)  → URL signée
 *   GET /api/lecons/{lecon}/stream/{type} (signed)         → flux du fichier
 */
class LeconMediaController extends Controller
{
    /** Types de média acceptés dans l'URL. */
    private const TYPES = ['pdf', 'video'];

    /**
     * Renvoie une URL de lecture fraîche.
     *
     * Utile pour les vidéos longues : le lecteur peut redemander une URL quand
     * la précédente approche de son expiration.
     */
    public function refresh(Request $request, int $id): JsonResponse
    {
        $lecon = Lecon::with('module.cours')->findOrFail($id);
        $type = $request->query('type', $lecon->type_contenu);

        if (! in_array($type, self::TYPES, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Type de média invalide.',
            ], 422);
        }

        if (! $this->peutAcceder($lecon)) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être inscrit à ce cours pour accéder à ce contenu.',
            ], 403);
        }

        $url = $lecon->urlMediaSignee($type);

        if ($url === null) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun média disponible pour cette leçon.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'url' => $url,
                'type' => $type,
                // Le client sait ainsi quand redemander une URL.
                'expire_dans' => Lecon::MINUTES_VALIDITE_URL * 60,
                'est_externe' => ! $lecon->estMediaHeberge($type),
            ],
        ]);
    }

    /**
     * Sert le fichier lui-même. L'accès est validé par la signature de l'URL.
     */
    public function stream(Lecon $lecon, string $type): BinaryFileResponse
    {
        abort_unless(in_array($type, self::TYPES, true), 404);

        $chemin = $lecon->cheminMediaRelatif($type);
        abort_if($chemin === null, 404, 'Média introuvable.');

        $disque = Storage::disk(Lecon::DISQUE_PRIVE);
        abort_unless($disque->exists($chemin), 404, 'Fichier introuvable.');

        $reponse = response()->file($disque->path($chemin), [
            'Content-Type' => $disque->mimeType($chemin) ?: $this->mimeParDefaut($type),
            // Empêche la mise en cache d'un contenu protégé par un proxy partagé.
            'Cache-Control' => 'private, max-age=0, no-store',
            'X-Content-Type-Options' => 'nosniff',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);

        // « inline » et non « attachment » : le navigateur affiche au lieu de
        // déclencher un téléchargement.
        $reponse->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            basename($chemin),
        );

        return $reponse;
    }

    /**
     * Un cours gratuit est ouvert ; sinon l'inscription est obligatoire.
     * Le formateur propriétaire et les administrateurs gardent l'accès.
     */
    private function peutAcceder(Lecon $lecon): bool
    {
        $cours = $lecon->module?->cours;

        if ($cours === null) {
            return false;
        }

        if ($cours->est_gratuit) {
            return true;
        }

        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        if ($user->role === 'admin' || (int) $cours->formateur_id === (int) $user->id) {
            return true;
        }

        return Inscription::where('apprenant_id', $user->id)
            ->where('cours_id', $cours->id)
            ->exists();
    }

    private function mimeParDefaut(string $type): string
    {
        return $type === 'pdf' ? 'application/pdf' : 'video/mp4';
    }
}
