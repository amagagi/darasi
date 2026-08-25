<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Platform;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PlatformController extends Controller
{
    /**
     * Plateformes actives, triées par ordre d'affichage. Filtrable par
     * catégorie. Mise en cache uniquement pour la liste non filtrée (la
     * seule requête chaude) ; un filtre par catégorie recalcule à chaque
     * fois, le volume de plateformes ne le justifie pas encore.
     */
    public function index(Request $request): JsonResponse
    {
        $categorie = $request->query('category');

        if ($categorie) {
            $plateformes = Platform::query()
                ->active()
                ->where('category', $categorie)
                ->ordered()
                ->get()
                ->map(fn (Platform $p) => $this->formater($p));
        } else {
            // ->toArray() avant mise en cache : voir le commentaire équivalent
            // dans PartnerController::index() (Collection sérialisée via le
            // pilote "database" pouvant revenir en __PHP_Incomplete_Class).
            $plateformes = Cache::remember('platforms.active', now()->addMinutes(10), function () {
                return Platform::query()
                    ->active()
                    ->ordered()
                    ->get()
                    ->map(fn (Platform $p) => $this->formater($p))
                    ->values()
                    ->toArray();
            });
        }

        return response()->json([
            'success' => true,
            'data' => $plateformes,
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $plateforme = Platform::query()->active()->where('slug', $slug)->first();

        if (! $plateforme) {
            return response()->json([
                'success' => false,
                'message' => 'Plateforme introuvable.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formater($plateforme, avecDescription: true),
        ]);
    }

    private function formater(Platform $plateforme, bool $avecDescription = false): array
    {
        $donnees = [
            'id' => $plateforme->id,
            'name' => $plateforme->name,
            'slug' => $plateforme->slug,
            'short_description' => $plateforme->short_description,
            'logo' => $plateforme->logo_path ? asset('storage/'.$plateforme->logo_path) : null,
            'cover_image' => $plateforme->cover_image_path ? asset('storage/'.$plateforme->cover_image_path) : null,
            'url' => $plateforme->url,
            'category' => $plateforme->category,
        ];

        if ($avecDescription) {
            $donnees['description'] = $plateforme->description;
        }

        return $donnees;
    }
}
