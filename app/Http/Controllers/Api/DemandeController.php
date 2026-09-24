<?php
// app/Http/Controllers/Api/DemandeController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DemandesFormation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * CONTROLLER DES DEMANDES DE FORMATION
 * 
 * @description Permet aux visiteurs de demander des formations spécifiques
 * @author amagagi
 * @version 1.0
 */
class DemandeController extends Controller
{
    /**
     * Enregistrer une demande de formation
     * 
     * @method POST
     * @endpoint /api/demandes-formation
     * @access Public (pas besoin d'authentification)
     * 
     * @body_param string nom required - Nom du demandeur
     * @body_param string email required - Email du demandeur
     * @body_param string telephone optional - Téléphone
     * @body_param string titre_cours_souhaite required - Titre du cours souhaité
     * @body_param string description optional - Description détaillée
     * @body_param string domaine optional - Domaine (IT, Scolaire, Etudiant)
     * @body_param string niveau_souhaite optional - Niveau souhaité
     * 
     * @response 201 {
     *   "success": true,
     *   "message": "Demande envoyée avec succès",
     *   "data": {...}
     * }
     * 
     * @response 422 {
     *   "errors": {...}
     * }
     */
    public function store(Request $request)
    {   
        // 🔍 LOGS TEMPORAIRES DE DEBUG
        \Log::info('=== DEMANDE STORE ===');
        \Log::info('Content-Type: ' . $request->header('Content-Type'));
        \Log::info('Method: ' . $request->method());
        \Log::info('All: ' . json_encode($request->all()));
        \Log::info('Raw: ' . $request->getContent());
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:formation,assistance',
            'nom' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'telephone' => 'nullable|string|max:20',
            'titre_cours_souhaite' => 'required|string|max:200',
            'description' => 'nullable|string',
            'domaine' => 'nullable|string|max:100',
            'niveau_souhaite' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $demande = DemandesFormation::create([
            'type' => $request->type,  // 🆕 AJOUT
            'nom' => $request->nom,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'titre_cours_souhaite' => $request->titre_cours_souhaite,
            'description' => $request->description,
            'domaine' => $request->domaine,
            'niveau_souhaite' => $request->niveau_souhaite,
            'statut' => 'en_attente',
        ]);

        // Message adaptatif selon le type
        if ($request->type === 'assistance') {
            return response()->json([
                'success' => true,
                'message' => 'Votre demande d\'assistance a été enregistrée. Redirection vers notre canal WhatsApp...',
                'data' => $demande,
                'whatsapp_url' => config('darasi.whatsapp_assistance_url'),
            ], 201);
        }

        return response()->json([
            'success' => true,
            'message' => 'Votre demande de formation a été envoyée avec succès. Nous vous contacterons prochainement.',
            'data' => $demande,
            'whatsapp_url' => null,
        ], 201);
    }
}