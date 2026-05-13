<?php
// app/Http/Controllers/Api/FormateurController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cours;
use App\Models\Inscription;
use App\Models\ForumDiscussion;
use App\Models\AutorisationCorrection;
use App\Models\ForumReponse;
use App\Models\TentativeTest;
use App\Models\CorrectionOuverte;
use App\Models\ReponseQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormateurController extends Controller
{
    /**
     * Vérifier que l'utilisateur est formateur
     */
    private function checkFormateur()
    {
        if (auth()->user()->role !== 'formateur') {
            abort(403, 'Accès non autorisé - Réservé aux formateurs');
        }
    }

    /**
     * Dashboard formateur
     * 
     * @method GET
     * @endpoint /api/formateur/dashboard
     */
        public function dashboard()
    {
        $this->checkFormateur();
        
        $user = auth()->user();
        
        // Récupérer les IDs des cours du formateur
        $coursIds = Cours::where('formateur_id', $user->id)->pluck('id');
        
        // Statistiques
        $totalCours = $coursIds->count();
        $totalApprenants = Inscription::whereIn('cours_id', $coursIds)->count();
        $totalQuestions = ForumDiscussion::whereIn('cours_id', $coursIds)->count();
        
        // Cours récents
        $coursRecents = Cours::where('formateur_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => [
                'statistiques' => [
                    'total_cours' => $totalCours,
                    'total_apprenants' => $totalApprenants,
                    'questions' => $totalQuestions,
                    'corrections_en_attente' => 0,
                ],
                'cours_recents' => $coursRecents
            ]
        ]);
    }

        /**
     * Statistiques détaillées des cours du formateur
     * 
     * @method GET
     * @endpoint /api/formateur/stats
     */
    public function statistiques()
    {
        $this->checkFormateur();
        
        $cours = Cours::where('formateur_id', auth()->id())
            ->withCount('inscriptions')
            ->get();
        
        if ($cours->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'message' => 'Aucun cours pour le moment',
                    'cours' => []
                ]
            ]);
        }
        
        // Cours le plus suivi
        $coursPlusSuivi = $cours->sortByDesc('inscriptions_count')->first();
        
        // Cours le moins suivi
        $coursMoinsSuivi = $cours->sortBy('inscriptions_count')->first();
        
        // Données pour graphique
        $graphique = $cours->map(function($c) {
            return [
                'cours_id' => $c->id,
                'titre' => $c->titre,
                'nb_apprenants' => $c->inscriptions_count
            ];
        });
        
        // Moyenne d'apprenants par cours
        $moyenne = $cours->avg('inscriptions_count');
        
        return response()->json([
            'success' => true,
            'data' => [
                'statistiques' => [
                    'total_cours' => $cours->count(),
                    'total_apprenants' => $cours->sum('inscriptions_count'),
                    'moyenne_apprenants_par_cours' => round($moyenne, 1),
                ],
                'top_cours' => [
                    'plus_suivi' => [
                        'cours_id' => $coursPlusSuivi->id,
                        'titre' => $coursPlusSuivi->titre,
                        'nb_apprenants' => $coursPlusSuivi->inscriptions_count
                    ],
                    'moins_suivi' => [
                        'cours_id' => $coursMoinsSuivi->id,
                        'titre' => $coursMoinsSuivi->titre,
                        'nb_apprenants' => $coursMoinsSuivi->inscriptions_count
                    ]
                ],
                'graphique' => $graphique
            ]
        ]);
    }

    /**
     * Liste des cours du formateur
     * 
     * @method GET
     * @endpoint /api/formateur/cours
     */
    public function mesCours()
    {
        $this->checkFormateur();
        
        $cours = Cours::where('formateur_id', auth()->id())
            ->withCount('inscriptions')
            ->with(['pole'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $cours
        ]);
    }

    /**
     * Détail d'un cours (formateur)
     * 
     * @method GET
     * @endpoint /api/formateur/cours/{id}
     */
    public function showCours($id)
    {
        $this->checkFormateur();
        
        $cours = Cours::where('formateur_id', auth()->id())
            ->with(['pole', 'modules.lecons'])
            ->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $cours
        ]);
    }

    /**
     * Apprenants inscrits à un cours
     * 
     * @method GET
     * @endpoint /api/formateur/cours/{id}/apprenants
     */
    public function apprenantsCours($id)
    {
        $this->checkFormateur();
        
        $cours = Cours::where('formateur_id', auth()->id())->findOrFail($id);
        
        $apprenants = Inscription::where('cours_id', $id)
            ->with(['apprenant'])
            ->get()
            ->map(function($inscription) {
                return [
                    'id' => $inscription->apprenant->id,
                    'nom' => $inscription->apprenant->nom,
                    'prenom' => $inscription->apprenant->prenom,
                    'email' => $inscription->apprenant->email,
                    'progression' => $inscription->progression,
                    'date_inscription' => $inscription->created_at
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => [
                'cours' => [
                    'id' => $cours->id,
                    'titre' => $cours->titre
                ],
                'total' => $apprenants->count(),
                'apprenants' => $apprenants
            ]
        ]);
    }

    /**
     * Questions d'un cours (forum)
     * 
     * @method GET
     * @endpoint /api/formateur/cours/{id}/questions
     */
    public function questionsCours($id)
    {
        $this->checkFormateur();
        
        $cours = Cours::where('formateur_id', auth()->id())->findOrFail($id);
        
        $questions = ForumDiscussion::where('cours_id', $id)
            ->with(['apprenant', 'reponses'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($q) {
                return [
                    'id' => $q->id,
                    'titre' => $q->titre,
                    'contenu' => $q->contenu,
                    'apprenant' => $q->apprenant->nom . ' ' . $q->apprenant->prenom,
                    'est_resolu' => $q->est_resolu,
                    'nb_reponses' => $q->reponses->count(),
                    'created_at' => $q->created_at
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => $questions
        ]);
    }

    /**
     * Répondre à une question
     * 
     * @method POST
     * @endpoint /api/formateur/questions/{id}/repondre
     */
    public function repondreQuestion(Request $request, $id)
    {
        $this->checkFormateur();
        
        $request->validate([
            'contenu' => 'required|string|min:3'
        ]);
        
        $question = ForumDiscussion::with('cours')->findOrFail($id);
        
        // Vérifier que le formateur est bien celui du cours
        if ($question->cours->formateur_id !== auth()->id()) {
            return response()->json([
                'error' => 'Vous ne pouvez pas répondre à cette question'
            ], 403);
        }
        
        $reponse = ForumReponse::create([
            'discussion_id' => $id,
            'formateur_id' => auth()->id(),
            'contenu' => $request->contenu
        ]);
        
        // Créer une notification pour l'apprenant
        \App\Models\Notification::create([
            'user_id' => $question->apprenant_id,
            'titre' => 'Réponse à votre question',
            'message' => 'Le formateur a répondu à votre question : ' . $question->titre,
            'type' => 'forum'
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Réponse envoyée',
            'data' => $reponse
        ], 201);
    }

    /**
     * Marquer une question comme résolue
     * 
     * @method PUT
     * @endpoint /api/formateur/questions/{id}/resoudre
     */
    public function resoudreQuestion($id)
    {
        $this->checkFormateur();
        
        $question = ForumDiscussion::with('cours')->findOrFail($id);
        
        if ($question->cours->formateur_id !== auth()->id()) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }
        
        $question->update(['est_resolu' => true]);
        
        return response()->json([
            'success' => true,
            'message' => 'Question marquée comme résolue'
        ]);
    }

    /**
     * Tentatives d'un quiz
     * 
     * @method GET
     * @endpoint /api/formateur/tentatives/{quiz_id}
     */
    public function tentativesQuiz($quiz_id)
    {
        $this->checkFormateur();
        
        $tentatives = TentativeTest::where('test_id', $quiz_id)
            ->with(['inscription.apprenant'])
            ->orderBy('date_tentative', 'desc')
            ->get()
            ->map(function($t) {
                return [
                    'id' => $t->id,
                    'apprenant' => $t->inscription->apprenant->nom . ' ' . $t->inscription->apprenant->prenom,
                    'note' => $t->note,
                    'est_valide' => $t->est_valide,
                    'date_tentative' => $t->date_tentative
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => $tentatives
        ]);
    }

    /**
     * Corriger une question ouverte
     * 
     * @method POST
     * @endpoint /api/formateur/correction/{reponse_id}
     */
    public function corrigerQuestion(Request $request, $reponse_id)
    {
        $this->checkFormateur();
        
        $request->validate([
            'note' => 'required|numeric|min:0',
            'commentaire' => 'nullable|string'
        ]);
        
        $reponse = ReponseQuestion::with(['question.test.module.cours'])->findOrFail($reponse_id);
        
        $cours = $reponse->question->test->module->cours;
        
        // Vérifier que le formateur est celui du cours
        if ($cours->formateur_id !== auth()->id() && auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Non autorisé - Ce cours ne vous appartient pas'], 403);
        }
        
        // Vérifier si le formateur est autorisé à corriger (sauf si admin)
        if (auth()->user()->role !== 'admin') {
            $estAutorise = AutorisationCorrection::where('formateur_id', auth()->id())
                ->where('cours_id', $cours->id)
                ->where('est_active', true)
                ->exists();
            
            if (!$estAutorise) {
                return response()->json([
                    'error' => 'Vous n\'êtes pas autorisé à corriger les questions ouvertes de ce cours'
                ], 403);
            }
        }
        
        $correction = CorrectionOuverte::create([
            'reponse_id' => $reponse_id,
            'note_accordee' => $request->note,
            'commentaire' => $request->commentaire,
            'corrige_par' => auth()->id()
        ]);
        
        $reponse->update([
            'points_obtenus' => $request->note,
            'est_correcte' => true
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Correction enregistrée',
            'data' => $correction
        ], 201);
    }
}