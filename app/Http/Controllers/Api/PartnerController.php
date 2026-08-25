<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class PartnerController extends Controller
{
    /**
     * Partenaires actifs, triés par ordre d'affichage. Mis en cache 10 min,
     * invalidé dès qu'un partenaire est créé/modifié/supprimé (voir
     * Partner::booted()).
     */
    public function index(): JsonResponse
    {
        // ->toArray() avant la mise en cache : le pilote "database" sérialise
        // via serialize()/unserialize(), et un objet Collection peut revenir
        // en __PHP_Incomplete_Class au lieu d'une vraie liste selon l'état de
        // l'autoloader au moment de la lecture — un tableau PHP simple n'a
        // pas ce problème.
        $partenaires = Cache::remember('partners.active', now()->addMinutes(10), function () {
            return Partner::query()
                ->active()
                ->ordered()
                ->get()
                ->map(fn (Partner $p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'logo' => asset('storage/'.$p->logo_path),
                    'website_url' => $p->website_url,
                    'description' => $p->description,
                ])
                ->values()
                ->toArray();
        });

        return response()->json([
            'success' => true,
            'data' => $partenaires,
        ]);
    }
}
