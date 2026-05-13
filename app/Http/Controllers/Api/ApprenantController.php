<?php
// app/Http/Controllers/Api/ApprenantController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inscription;
use App\Models\ProgresLecon;
use App\Models\Cours;
use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * CONTROLLER APPRENANT
 * 
 * @description Gère le tableau de bord, la progression, les messages et les notifications des apprenants
 * @author amagagi
 * @version 1.0
 */
class ApprenantController extends Controller
{
    /**
     * Tableau de bord de l'apprenant
     * 
     * @method GET
     * @endpoint /api/apprenant/dashboard
     * @requires Auth (Bearer Token)
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "cours_en_cours": [...],
     *     "cours_termines": [...],
     *     "certificats_obtenus": 2,
     *     "progression_globale": 45.5,
     *     "messages_non_lus": 3
     *   }
     * }
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        
        // Récupérer toutes les inscriptions avec leurs cours
        $inscriptions = Inscription::where('apprenant_id', $user->id)
            ->with('cours')
            ->get();
        
        // Séparer les cours en cours vs terminés
        $coursEnCours = [];
        $coursTermines = [];
        $totalProgression = 0;
        
        foreach ($inscriptions as $inscription) {
            if ($inscription->statut === 'termine' || $inscription->progression >= 100) {
                $coursTermines[] = $inscription;
            } else {
                $coursEnCours[] = $inscription;
            }
            $totalProgression += $inscription->progression;
        }
        
        // Compter les certificats obtenus
        $certificatsObtenus = $user->certificats()->count();
        
        // Compter les messages non lus
        $messagesNonLus = Message::where('destinataire_id', $user->id)
            ->where('est_lu', false)
            ->count();
        
        // Progression moyenne
        $nbCours = count($inscriptions);
        $progressionGlobale = $nbCours > 0 ? $totalProgression / $nbCours : 0;
        
        return response()->json([
            'success' => true,
            'data' => [
                'cours_en_cours' => $coursEnCours,
                'cours_termines' => $coursTermines,
                'certificats_obtenus' => $certificatsObtenus,
                'progression_globale' => round($progressionGlobale, 2),
                'messages_non_lus' => $messagesNonLus
            ]
        ]);
    }
    
    /**
     * Progression détaillée d'un cours spécifique
     * 
     * @method GET
     * @endpoint /api/apprenant/progression/{cours_id}
     * @requires Auth (Bearer Token)
     * 
     * @url_param int cours_id required - ID du cours
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "cours_id": 1,
     *     "cours_titre": "Laravel",
     *     "progression": 45.5,
     *     "lecons_completes": 5,
     *     "total_lecons": 12,
     *     "derniere_lecon": {...}
     *   }
     * }
     */
    public function progressionCours(Request $request, $cours_id)
    {
        $user = $request->user();
        
        $inscription = Inscription::where('apprenant_id', $user->id)
            ->where('cours_id', $cours_id)
            ->with('cours')
            ->first();
        
        if (!$inscription) {
            return response()->json([
                'error' => 'Vous n\'êtes pas inscrit à ce cours'
            ], 403);
        }
        
        // Compter le nombre total de leçons du cours
        $totalLecons = ProgresLecon::whereHas('inscription', function($q) use ($user, $cours_id) {
            $q->where('apprenant_id', $user->id)->where('cours_id', $cours_id);
        })->count();
        
        // Compter les leçons complétées
        $leconsCompletes = ProgresLecon::whereHas('inscription', function($q) use ($user, $cours_id) {
            $q->where('apprenant_id', $user->id)->where('cours_id', $cours_id);
        })->where('est_complete', true)->count();
        
        // Dernière leçon consultée
        $derniereLecon = ProgresLecon::whereHas('inscription', function($q) use ($user, $cours_id) {
            $q->where('apprenant_id', $user->id)->where('cours_id', $cours_id);
        })->latest()->first();
        
        return response()->json([
            'success' => true,
            'data' => [
                'cours_id' => $cours_id,
                'cours_titre' => $inscription->cours->titre,
                'progression' => $inscription->progression,
                'lecons_completes' => $leconsCompletes,
                'total_lecons' => $totalLecons,
                'derniere_lecon' => $derniereLecon ? [
                    'id' => $derniereLecon->lecon_id,
                    'date' => $derniereLecon->date_completion
                ] : null
            ]
        ]);
    }
    
    /**
     * Envoyer un message au formateur
     * 
     * @method POST
     * @endpoint /api/apprenant/messages/envoyer
     * @requires Auth (Bearer Token)
     * 
     * @body_param int cours_id required - ID du cours
     * @body_param string contenu required - Message
     * 
     * @response 201 {
     *   "success": true,
     *   "message": "Message envoyé au formateur",
     *   "data": {...}
     * }
     */
    public function envoyerMessage(Request $request)
    {
        $request->validate([
            'cours_id' => 'required|exists:cours,id',
            'contenu' => 'required|string|min:3'
        ]);
        
        $user = $request->user();
        
        // Vérifier que l'apprenant est inscrit au cours
        $inscription = Inscription::where('apprenant_id', $user->id)
            ->where('cours_id', $request->cours_id)
            ->exists();
        
        if (!$inscription) {
            return response()->json([
                'error' => 'Vous devez être inscrit à ce cours pour envoyer un message.'
            ], 403);
        }
        
        // Récupérer le formateur du cours
        $cours = Cours::findOrFail($request->cours_id);
        $formateurId = $cours->formateur_id;
        
        // Créer le message
        $message = Message::create([
            'expediteur_id' => $user->id,
            'destinataire_id' => $formateurId,
            'cours_id' => $request->cours_id,
            'contenu' => $request->contenu,
            'est_lu' => false
        ]);
        
        // Créer une notification pour le formateur
        Notification::create([
            'user_id' => $formateurId,
            'titre' => 'Nouveau message',
            'message' => "{$user->prenom} {$user->nom} vous a envoyé un message concernant le cours '{$cours->titre}'",
            'type' => 'systeme',
            'data' => json_encode([
                'type' => 'message',
                'message_id' => $message->id,
                'cours_id' => $request->cours_id
            ])
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Message envoyé au formateur',
            'data' => $message
        ], 201);
    }
    
    /**
     * Récupérer tous les messages de l'utilisateur
     * 
     * @method GET
     * @endpoint /api/apprenant/messages
     * @requires Auth (Bearer Token)
     */
    public function mesMessages(Request $request)
    {
        $user = $request->user();
        
        $messages = Message::where('destinataire_id', $user->id)
            ->orWhere('expediteur_id', $user->id)
            ->with(['expediteur', 'destinataire', 'cours'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }
    
    /**
     * Marquer un message comme lu
     * 
     * @method POST
     * @endpoint /api/apprenant/messages/{id}/lire
     * @requires Auth (Bearer Token)
     */
    public function marquerMessageLu($id, Request $request)
    {
        $user = $request->user();
        
        $message = Message::where('id', $id)
            ->where('destinataire_id', $user->id)
            ->firstOrFail();
        
        $message->marquerCommeLu();
        
        return response()->json([
            'success' => true,
            'message' => 'Message marqué comme lu'
        ]);
    }
    
    /**
     * Récupérer les notifications
     * 
     * @method GET
     * @endpoint /api/apprenant/notifications
     * @requires Auth (Bearer Token)
     */
    public function mesNotifications(Request $request)
    {
        $user = $request->user();
        
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $nonLues = $notifications->where('est_lu', false)->count();
        
        return response()->json([
            'success' => true,
            'non_lues' => $nonLues,
            'data' => $notifications
        ]);
    }
    
    /**
     * Marquer une notification comme lue
     * 
     * @method POST
     * @endpoint /api/apprenant/notifications/{id}/lire
     * @requires Auth (Bearer Token)
     */
    public function marquerNotificationLue($id, Request $request)
    {
        $user = $request->user();
        
        $notification = Notification::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        $notification->update(['est_lu' => true]);
        
        return response()->json([
            'success' => true,
            'message' => 'Notification marquée comme lue'
        ]);
    }
}