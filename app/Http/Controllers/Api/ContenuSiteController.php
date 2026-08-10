<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContenuSite;
use Illuminate\Http\JsonResponse;

class ContenuSiteController extends Controller
{
    /**
     * Blocs éditoriaux actifs de la vitrine (vision, mission, valeurs...).
     */
    public function index(): JsonResponse
    {
        $contenus = ContenuSite::query()
            ->actif()
            ->orderBy('ordre')
            ->orderBy('id')
            ->get()
            ->map(fn (ContenuSite $c) => [
                'cle' => $c->cle,
                'titre' => $c->titre,
                'sous_titre' => $c->sous_titre,
                'contenu' => $c->contenu,
                'icone' => $c->icone,
                'ordre' => $c->ordre,
            ]);

        return response()->json([
            'success' => true,
            'data' => $contenus,
        ]);
    }
}
