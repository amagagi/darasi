<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Annonce;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnonceController extends Controller
{
    /**
     * Liste paginée des actualités publiées (section « Actualités »).
     */
    public function index(Request $request): JsonResponse
    {
        $cible = $this->cibleDepuis($request);

        $annonces = Annonce::query()
            ->active()
            ->pourCible($cible)
            ->where('afficher_actualites', true)
            ->ordreAffichage()
            ->paginate(min((int) $request->input('par_page', 10), 50));

        return response()->json([
            'success' => true,
            'data' => [
                'data' => $annonces->getCollection()
                    ->map(fn (Annonce $a) => $this->formater($a, avecContenu: true))
                    ->values(),
                'current_page' => $annonces->currentPage(),
                'last_page' => $annonces->lastPage(),
                'total' => $annonces->total(),
            ],
        ]);
    }

    /**
     * Annonces destinées au bandeau d'alerte, triées par priorité.
     *
     * Le frontend n'en affiche qu'une à la fois mais reçoit la liste complète
     * pour pouvoir passer à la suivante quand l'utilisateur en masque une.
     */
    public function banniere(Request $request): JsonResponse
    {
        $cible = $this->cibleDepuis($request);

        $annonces = Annonce::query()
            ->active()
            ->pourCible($cible)
            ->where('afficher_banniere', true)
            ->ordreAffichage()
            ->limit(5)
            ->get()
            ->map(fn (Annonce $a) => $this->formater($a));

        return response()->json([
            'success' => true,
            'data' => $annonces,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $annonce = Annonce::query()->active()->find($id);

        if (! $annonce) {
            return response()->json([
                'success' => false,
                'message' => 'Actualité introuvable.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formater($annonce, avecContenu: true),
        ]);
    }

    /**
     * Une requête authentifiée voit les annonces `connectes`, un visiteur celles
     * marquées `public`. Dans les deux cas les annonces `tous` sont incluses.
     *
     * Ces routes sont publiques : on résout le garde Sanctum à la main pour
     * détecter un éventuel jeton sans jamais rejeter la requête.
     */
    private function cibleDepuis(Request $request): string
    {
        return auth('sanctum')->user() ? 'connectes' : 'public';
    }

    private function formater(Annonce $annonce, bool $avecContenu = false): array
    {
        $donnees = [
            'id' => $annonce->id,
            'titre' => $annonce->titre,
            'extrait' => $annonce->extrait,
            'type' => $annonce->type,
            'est_permanente' => $annonce->est_permanente,
            'lien_url' => $annonce->lien_url,
            'lien_libelle' => $annonce->lien_libelle,
            'image' => $annonce->image ? asset('storage/'.$annonce->image) : null,
            'publiee_le' => optional($annonce->publiee_le ?? $annonce->created_at)->toIso8601String(),
            // Sert de clé de mémorisation du masquage côté client : une
            // modification de l'annonce la fait réapparaître.
            'version' => optional($annonce->updated_at)->timestamp,
        ];

        if ($avecContenu) {
            $donnees['contenu'] = $annonce->contenu;
        }

        return $donnees;
    }
}
