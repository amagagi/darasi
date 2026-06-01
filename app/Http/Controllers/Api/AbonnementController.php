<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbonnementSouscrit;
use App\Models\AbonnementType;
use App\Models\Paiement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AbonnementController extends Controller
{
    public function index()
    {
        $abonnements = AbonnementType::where('est_actif', true)
            ->withCount('cours')
            ->orderBy('ordre')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $abonnements,
        ]);
    }

    public function mesAbonnements(Request $request)
    {
        $abonnements = AbonnementSouscrit::where('apprenant_id', $request->user()->id)
            ->with('type')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $abonnements,
        ]);
    }

    public function souscrire(Request $request, int $id)
    {
        $user = $request->user();

        if ($user->role !== 'apprenant') {
            return response()->json([
                'success' => false,
                'message' => 'Seuls les apprenants peuvent souscrire a un abonnement.',
            ], 403);
        }

        $abonnement = AbonnementType::where('est_actif', true)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'mode_paiement' => 'required|in:CARTE,AIRTEL_MONEY,MY_NITA,AMANATA',
            'telephone' => 'required_unless:mode_paiement,CARTE|nullable|string|min:8',
            'card_holder' => 'required_if:mode_paiement,CARTE|nullable|string|max:255',
            'card_number' => 'required_if:mode_paiement,CARTE|nullable|string|min:16|max:19',
            'expiry_date' => 'required_if:mode_paiement,CARTE|nullable|string|size:5',
            'cvv' => 'required_if:mode_paiement,CARTE|nullable|string|size:3',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $active = AbonnementSouscrit::where('apprenant_id', $user->id)
            ->where('statut', 'actif')
            ->where('date_fin', '>=', now())
            ->first();

        if ($active) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez deja un abonnement actif.',
                'data' => $active->load('type'),
            ], 409);
        }

        return DB::transaction(function () use ($request, $user, $abonnement) {
            $paiement = Paiement::create([
                'apprenant_id' => $user->id,
                'abonnement_type_id' => $abonnement->id,
                'montant' => $abonnement->prix,
                'mode_paiement' => $request->mode_paiement,
                'statut' => 'paye',
                'date_paiement' => now(),
                'transaction_id' => 'DARASI_ABO_' . $user->id . '_' . $abonnement->id . '_' . time(),
                'tentatives' => 1,
            ]);

            $souscription = AbonnementSouscrit::create([
                'apprenant_id' => $user->id,
                'type_abonnement_id' => $abonnement->id,
                'date_debut' => now(),
                'date_fin' => now()->addDays($abonnement->duree_jours),
                'statut' => 'actif',
                'paiement_id' => $paiement->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Abonnement active avec succes.',
                'data' => $souscription->load('type', 'paiement'),
            ], 201);
        });
    }
}
