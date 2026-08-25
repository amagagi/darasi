<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class TestimonialController extends Controller
{
    /**
     * Témoignages actifs, triés par ordre d'affichage. Mêmes règles de cache
     * que PartnerController::index().
     */
    public function index(): JsonResponse
    {
        $temoignages = Cache::remember('testimonials.active', now()->addMinutes(10), function () {
            return Testimonial::query()
                ->active()
                ->ordered()
                ->get()
                ->map(fn (Testimonial $t) => [
                    'id' => $t->id,
                    'author_name' => $t->author_name,
                    'author_role' => $t->author_role,
                    'photo' => $t->photo_path ? asset('storage/'.$t->photo_path) : null,
                    'content' => $t->content,
                    'rating' => $t->rating,
                ])
                ->values();
        });

        return response()->json([
            'success' => true,
            'data' => $temoignages,
        ]);
    }
}
