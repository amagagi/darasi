<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Test;
use App\Models\Inscription;
use App\Models\ProgresLecon;
use App\Models\Lecon;
use App\Models\ConfigTentative;
use App\Models\TentativeTest;
use App\Models\ReponseQuestion;
use App\Models\Question;
use App\Models\ChoixQuestion;
use App\Models\Notification;
use App\Models\Cours;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * CONTROLLER DES TESTS DE MODULE
 * 
 * @description Gère l'accès aux tests de module, la soumission des réponses et les résultats
 * @author amagagi
 * @version 1.0
 */
class TestController extends Controller
{
    /**
     * Récupérer les questions d'un test de module
     * 
     * @method GET
     * @endpoint /api/tests/{testId}/questions
     * @requires Auth (Bearer Token)
     * 
     * @url_param int testId required - ID du test
     * 
     * Vérifications :
     * - Inscription au cours
     * - Toutes les leçons du module complétées
     * - Nombre de tentatives (max 3)
     * - Délai entre tentatives (24h)
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "test": {
     *       "id": 1,
     *       "titre": "Test module 1",
     *       "duree_limite": 15
     *     },
     *     "tentative_numero": 1,
     *     "max_tentatives": 3,
     *     "questions": [
     *       {
     *         "id": 1,
     *         "question": "Qu'est-ce que Laravel ?",
     *         "type": "qcm",
     *         "points": 2,
     *         "ordre": 1,
     *         "choix": [
     *           {"id": 1, "texte": "...", "ordre": 1},
     *           {"id": 2, "texte": "...", "ordre": 2}
     *         ]
     *       },
     *       {
     *         "id": 2,
     *         "question": "Expliquez le MVC",
     *         "type": "ouverte",
     *         "points": 4,
     *         "ordre": 2
     *       }
     *     ]
     *   }
     * }
     * 
     * @response 403 {
     *   "error": "Vous devez être inscrit à ce cours"
     * }
     * 
     * @response 403 {
     *   "error": "Vous devez terminer toutes les leçons de ce module avant de passer le test",
     *   "lecons_restantes": 2
     * }
     * 
     * @response 403 {
     *   "error": "Vous avez atteint le nombre maximum de tentatives",
     *   "max_tentatives": 3
     * }
     * 
     * @response 403 {
     *   "error": "Vous devez attendre avant de pouvoir retenter le test",
     *   "prochaine_disponible": "2026-07-08 10:00:00"
     * }
     */
    public function getQuestions($testId)
    {
        $user = auth()->user();
        
        // Récupérer le test avec son module et son cours
        $test = Test::with(['module.cours'])->findOrFail($testId);
        $module = $test->module;
        $cours = $module->cours;
        
        // 1. Vérifier l'inscription
        $inscription = Inscription::where('apprenant_id', $user->id)
            ->where('cours_id', $cours->id)
            ->where('statut', 'actif')
            ->first();
        
        if (!$inscription) {
            return response()->json([
                'error' => 'Vous devez être inscrit à ce cours'
            ], 403);
        }
        
        // 2. Vérifier que toutes les leçons du module sont complétées
        $totalLecons = Lecon::where('module_id', $module->id)->count();
        $leconsCompletees = ProgresLecon::whereHas('inscription', function($q) use ($inscription) {
            $q->where('id', $inscription->id);
        })->where('est_complete', true)->count();
        
        if ($leconsCompletees < $totalLecons) {
            return response()->json([
                'error' => 'Vous devez terminer toutes les leçons de ce module avant de passer le test',
                'lecons_restantes' => $totalLecons - $leconsCompletees
            ], 403);
        }
        
        // 3. Vérifier les tentatives
        $config = ConfigTentative::where('test_id', $testId)->first();
        $maxTentatives = $config ? $config->max_tentatives : 3;
        $delaiHeures = $config ? $config->delai_heures : 24;
        
        $tentativesCount = TentativeTest::where('inscription_id', $inscription->id)
            ->where('test_id', $testId)
            ->count();
        
        if ($tentativesCount >= $maxTentatives) {
            return response()->json([
                'error' => 'Vous avez atteint le nombre maximum de tentatives',
                'max_tentatives' => $maxTentatives
            ], 403);
        }
        
        // 4. Vérifier le délai entre les tentatives
        if ($tentativesCount > 0) {
            $derniereTentative = TentativeTest::where('inscription_id', $inscription->id)
                ->where('test_id', $testId)
                ->latest()
                ->first();
            
            $prochaineAutorisee = $derniereTentative->date_tentative->addHours($delaiHeures);
            if (now()->lt($prochaineAutorisee)) {
                return response()->json([
                    'error' => 'Vous devez attendre avant de pouvoir retenter le test',
                    'prochaine_disponible' => $prochaineAutorisee->toDateTimeString()
                ], 403);
            }
        }
        
        // 5. Récupérer les questions avec leurs choix (sans est_correct)
        $questions = Question::where('test_id', $testId)
            ->orderBy('ordre')
            ->get()
            ->map(function($question) {
                $data = [
                    'id' => $question->id,
                    'question' => $question->question,
                    'type' => $question->type,
                    'points' => $question->points,
                    'ordre' => $question->ordre
                ];
                
                // Pour les QCM, récupérer les choix (sans est_correct)
                if ($question->type === 'qcm') {
                    $data['choix'] = ChoixQuestion::where('question_id', $question->id)
                        ->orderBy('ordre')
                        ->get()
                        ->map(function($choix) {
                            return [
                                'id' => $choix->id,
                                'texte' => $choix->texte,
                                'ordre' => $choix->ordre
                            ];
                        });
                }
                
                return $data;
            });
        
        return response()->json([
            'success' => true,
            'data' => [
                'test' => [
                    'id' => $test->id,
                    'titre' => $test->titre,
                    'duree_limite' => $test->duree_limite
                ],
                'tentative_numero' => $tentativesCount + 1,
                'max_tentatives' => $maxTentatives,
                'questions' => $questions
            ]
        ]);
    }
    
    /**
     * Soumettre les réponses d'un test de module
     * 
     * @method POST
     * @endpoint /api/tests/{testId}/submit
     * @requires Auth (Bearer Token)
     * 
     * @url_param int testId required - ID du test
     * 
     * @body_param array reponses required - Liste des réponses
     * @body_param int reponses[].question_id required - ID de la question
     * @body_param int reponses[].choix_id optional - ID du choix (pour QCM)
     * @body_param string reponses[].reponse_texte optional - Réponse texte (pour questions ouvertes)
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "Test soumis avec succès",
     *   "data": {
     *     "tentative_id": 1,
     *     "note": 14.5,
     *     "est_valide": true,
     *     "points_obtenus": 8,
     *     "total_points": 10,
     *     "a_questions_ouvertes": true,
     *     "message_ouvertes": "Certaines questions sont en attente de correction par le formateur."
     *   }
     * }
     * 
     * @response 422 {
     *   "error": "Validation des données",
     *   "errors": {...}
     * }
     */
    public function submit(Request $request, $testId)
    {
        $user = auth()->user();
        
        // Validation des données
        $request->validate([
            'reponses' => 'required|array',
            'reponses.*.question_id' => 'required|exists:questions,id',
            'reponses.*.choix_id' => 'nullable|exists:choix_questions,id',
            'reponses.*.reponse_texte' => 'nullable|string'
        ]);
        
        // Récupérer le test avec son module
        $test = Test::with(['module.cours'])->findOrFail($testId);
        $module = $test->module;
        $cours = $module->cours;
        
        // 1. Vérifier l'inscription
        $inscription = Inscription::where('apprenant_id', $user->id)
            ->where('cours_id', $cours->id)
            ->where('statut', 'actif')
            ->first();
        
        if (!$inscription) {
            return response()->json([
                'error' => 'Vous devez être inscrit à ce cours'
            ], 403);
        }
        
        // 2. Vérifier les leçons complétées
        $totalLecons = Lecon::where('module_id', $module->id)->count();
        $leconsCompletees = ProgresLecon::whereHas('inscription', function($q) use ($inscription) {
            $q->where('id', $inscription->id);
        })->where('est_complete', true)->count();
        
        if ($leconsCompletees < $totalLecons) {
            return response()->json([
                'error' => 'Vous devez terminer toutes les leçons de ce module avant de passer le test'
            ], 403);
        }
        
        // 3. Vérifier les tentatives
        $config = ConfigTentative::where('test_id', $testId)->first();
        $maxTentatives = $config ? $config->max_tentatives : 3;
        $delaiHeures = $config ? $config->delai_heures : 24;
        
        $tentativesCount = TentativeTest::where('inscription_id', $inscription->id)
            ->where('test_id', $testId)
            ->count();
        
        if ($tentativesCount >= $maxTentatives) {
            return response()->json([
                'error' => 'Vous avez atteint le nombre maximum de tentatives'
            ], 403);
        }
        
        // 4. Vérifier le délai
        if ($tentativesCount > 0) {
            $derniereTentative = TentativeTest::where('inscription_id', $inscription->id)
                ->where('test_id', $testId)
                ->latest()
                ->first();
            
            $prochaineAutorisee = $derniereTentative->date_tentative->addHours($delaiHeures);
            if (now()->lt($prochaineAutorisee)) {
                return response()->json([
                    'error' => 'Vous devez attendre avant de pouvoir retenter le test',
                    'prochaine_disponible' => $prochaineAutorisee->toDateTimeString()
                ], 403);
            }
        }
        
        DB::beginTransaction();
        
        try {
            // 5. Créer la tentative
            $tentative = TentativeTest::create([
                'inscription_id' => $inscription->id,
                'test_id' => $testId,
                'tentative_numero' => $tentativesCount + 1,
                'date_tentative' => now(),
                'note' => null,
                'est_valide' => false
            ]);
            
            $totalPoints = 0;
            $pointsObtenus = 0;
            $aQuestionsOuvertes = false;
            $questionsOuvertesIds = [];
            
            // 6. Traiter chaque réponse
            foreach ($request->reponses as $reponseData) {
                $question = Question::findOrFail($reponseData['question_id']);
                $totalPoints += $question->points;
                
                // Créer la réponse
                $reponse = ReponseQuestion::create([
                    'tentative_test_id' => $tentative->id,
                    'question_id' => $question->id,
                    'choix_id' => $reponseData['choix_id'] ?? null,
                    'reponse_texte' => $reponseData['reponse_texte'] ?? null,
                    'est_correcte' => false,
                    'points_obtenus' => 0
                ]);
                
                // Correction automatique pour les QCM
                if ($question->type === 'qcm' && isset($reponseData['choix_id'])) {
                    $choix = ChoixQuestion::find($reponseData['choix_id']);
                    $estCorrecte = $choix && $choix->est_correct;
                    
                    $reponse->update([
                        'est_correcte' => $estCorrecte,
                        'points_obtenus' => $estCorrecte ? $question->points : 0
                    ]);
                    
                    if ($estCorrecte) {
                        $pointsObtenus += $question->points;
                    }
                }
                
                // Questions ouvertes : en attente de correction
                if ($question->type === 'ouverte') {
                    $aQuestionsOuvertes = true;
                    $questionsOuvertesIds[] = $question->id;
                    // Notification envoyée après la transaction
                }
            }
            
            // 7. Calculer la note sur 20
            $note = $totalPoints > 0 ? round(($pointsObtenus / $totalPoints) * 20, 2) : 0;
            $estValide = $note >= 10;
            
            // 8. Mettre à jour la tentative
            $tentative->update([
                'note' => $note,
                'est_valide' => $estValide
            ]);
            
            // 9. Si le test est validé, marquer le module comme validé
            if ($estValide) {
                // Marquer le module comme validé dans l'inscription
                // On peut utiliser un champ JSON ou une table dédiée
                // Pour l'instant, on met à jour tests_modules_valides
                // Si vous avez une table module_validation, à adapter
                
                // Option : marquer dans inscriptions.tests_modules_valides
                // $inscription->update(['tests_modules_valides' => true]);
                
                // Ou créer une entrée dans une table module_validations
                // ModuleValidation::create([...]);
            }
            
            // 10. Notifier le formateur s'il y a des questions ouvertes
            if ($aQuestionsOuvertes) {
                $formateur = User::find($cours->formateur_id);
                if ($formateur) {
                    Notification::create([
                        'user_id' => $formateur->id,
                        'titre' => 'Questions ouvertes en attente de correction',
                        'message' => "{$user->prenom} {$user->nom} a soumis des réponses ouvertes pour le test '{$test->titre}' du cours '{$cours->titre}'. Veuillez les corriger.",
                        'type' => 'systeme',
                        'data' => json_encode([
                            'type' => 'correction_ouverte',
                            'tentative_id' => $tentative->id,
                            'cours_id' => $cours->id,
                            'test_id' => $testId,
                            'apprenant_id' => $user->id
                        ])
                    ]);
                }
            }
            
            DB::commit();
            
            // 11. Construire la réponse
            $response = [
                'tentative_id' => $tentative->id,
                'note' => $note,
                'est_valide' => $estValide,
                'points_obtenus' => $pointsObtenus,
                'total_points' => $totalPoints
            ];
            
            if ($aQuestionsOuvertes) {
                $response['a_questions_ouvertes'] = true;
                $response['message_ouvertes'] = 'Certaines questions sont en attente de correction par le formateur. Vous serez notifié dès que la correction sera effectuée.';
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Test soumis avec succès',
                'data' => $response
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Erreur lors de la soumission du test',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Récupérer les résultats détaillés d'une tentative
     * 
     * @method GET
     * @endpoint /api/tests/tentatives/{tentativeId}/results
     * @requires Auth (Bearer Token)
     * 
     * @url_param int tentativeId required - ID de la tentative
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "tentative_id": 1,
     *     "tentative_numero": 1,
     *     "date_tentative": "2026-07-07 10:00:00",
     *     "note": 14.5,
     *     "est_valide": true,
     *     "total_points": 10,
     *     "points_obtenus": 8,
     *     "reponses": [
     *       {
     *         "question_id": 1,
     *         "question": "Qu'est-ce que Laravel ?",
     *         "type": "qcm",
     *         "choix_choisi": "Un framework PHP",
     *         "est_correcte": true,
     *         "points_obtenus": 2,
     *         "points_max": 2
     *       },
     *       {
     *         "question_id": 2,
     *         "question": "Expliquez le MVC",
     *         "type": "ouverte",
     *         "reponse_texte": "MVC est un pattern...",
     *         "est_corrige": false,
     *         "points_obtenus": null,
     *         "points_max": 4,
     *         "statut": "en_attente"
     *       }
     *     ]
     *   }
     * }
     * 
     * @response 403 {
     *   "error": "Vous n'êtes pas autorisé à voir cette tentative"
     * }
     */
    public function getTentativeResults($tentativeId)
    {
        $user = auth()->user();
        
        $tentative = TentativeTest::with(['inscription', 'reponses.question.choix'])
            ->findOrFail($tentativeId);
        
        // Vérifier que l'utilisateur est bien l'apprenant concerné
        if ($tentative->inscription->apprenant_id !== $user->id) {
            return response()->json([
                'error' => 'Vous n\'êtes pas autorisé à voir cette tentative'
            ], 403);
        }
        
        $totalPoints = 0;
        $pointsObtenus = 0;
        $reponsesDetail = [];
        
        foreach ($tentative->reponses as $reponse) {
            $question = $reponse->question;
            $totalPoints += $question->points;
            $pointsObtenus += $reponse->points_obtenus;
            
            $detail = [
                'question_id' => $question->id,
                'question' => $question->question,
                'type' => $question->type,
                'points_max' => $question->points
            ];
            
            if ($question->type === 'qcm') {
                $choixChoisi = $reponse->choix ? $reponse->choix->texte : null;
                $detail['choix_choisi'] = $choixChoisi;
                $detail['est_correcte'] = $reponse->est_correcte;
                $detail['points_obtenus'] = $reponse->points_obtenus;
            } else {
                // Question ouverte
                $detail['reponse_texte'] = $reponse->reponse_texte;
                
                // Vérifier si la question a été corrigée
                $correction = $reponse->correctionOuverte;
                if ($correction) {
                    $detail['est_corrige'] = true;
                    $detail['points_obtenus'] = $correction->note_accordee;
                    $detail['commentaire'] = $correction->commentaire;
                    $detail['statut'] = 'corrigee';
                } else {
                    $detail['est_corrige'] = false;
                    $detail['points_obtenus'] = null;
                    $detail['statut'] = 'en_attente';
                }
            }
            
            $reponsesDetail[] = $detail;
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'tentative_id' => $tentative->id,
                'tentative_numero' => $tentative->tentative_numero,
                'date_tentative' => $tentative->date_tentative->toDateTimeString(),
                'note' => $tentative->note,
                'est_valide' => $tentative->est_valide,
                'total_points' => $totalPoints,
                'points_obtenus' => $pointsObtenus,
                'reponses' => $reponsesDetail
            ]
        ]);
    }
    
    /**
     * Vérifier si l'apprenant peut accéder au test
     * 
     * @method GET
     * @endpoint /api/tests/{testId}/accessible
     * @requires Auth (Bearer Token)
     * 
     * @url_param int testId required - ID du test
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "accessible": true,
     *     "reason": null,
     *     "lecons_restantes": 0,
     *     "tentatives_restantes": 2,
     *     "prochaine_disponible": null,
     *     "module_est_termine": true
     *   }
     * }
     */
    public function checkAccess($testId)
    {
        $user = auth()->user();
        
        $test = Test::with(['module.cours'])->findOrFail($testId);
        $module = $test->module;
        $cours = $module->cours;
        
        $result = [
            'accessible' => true,
            'reason' => null,
            'lecons_restantes' => 0,
            'tentatives_restantes' => 0,
            'prochaine_disponible' => null,
            'module_est_termine' => false
        ];
        
        // Vérifier l'inscription
        $inscription = Inscription::where('apprenant_id', $user->id)
            ->where('cours_id', $cours->id)
            ->where('statut', 'actif')
            ->first();
        
        if (!$inscription) {
            $result['accessible'] = false;
            $result['reason'] = 'Vous devez être inscrit à ce cours';
            return response()->json(['success' => true, 'data' => $result]);
        }
        
        // Vérifier les leçons complétées
        $totalLecons = Lecon::where('module_id', $module->id)->count();
        $leconsCompletees = ProgresLecon::whereHas('inscription', function($q) use ($inscription) {
            $q->where('id', $inscription->id);
        })->where('est_complete', true)->count();
        
        $leconsRestantes = $totalLecons - $leconsCompletees;
        if ($leconsRestantes > 0) {
            $result['accessible'] = false;
            $result['reason'] = 'Vous devez terminer toutes les leçons de ce module';
            $result['lecons_restantes'] = $leconsRestantes;
            return response()->json(['success' => true, 'data' => $result]);
        }
        $result['module_est_termine'] = true;
        
        // Vérifier les tentatives
        $validationExists = TentativeTest::where('inscription_id', $inscription->id)
            ->where('test_id', $testId)
            ->where('est_valide', true)
            ->exists();
        $result['est_valide'] = $validationExists;

        $config = ConfigTentative::where('test_id', $testId)->first();
        $maxTentatives = $config ? $config->max_tentatives : 3;
        $delaiHeures = $config ? $config->delai_heures : 24;
        
        $tentativesCount = TentativeTest::where('inscription_id', $inscription->id)
            ->where('test_id', $testId)
            ->count();
        
        $tentativesRestantes = $maxTentatives - $tentativesCount;
        $result['tentatives_restantes'] = $tentativesRestantes;
        
        if ($tentativesCount >= $maxTentatives) {
            $result['accessible'] = false;
            $result['reason'] = 'Vous avez atteint le nombre maximum de tentatives';
            return response()->json(['success' => true, 'data' => $result]);
        }
        
        // Vérifier le délai
        if ($tentativesCount > 0) {
            $derniereTentative = TentativeTest::where('inscription_id', $inscription->id)
                ->where('test_id', $testId)
                ->latest()
                ->first();
            
            $prochaineAutorisee = $derniereTentative->date_tentative->addHours($delaiHeures);
            if (now()->lt($prochaineAutorisee)) {
                $result['accessible'] = false;
                $result['reason'] = 'Vous devez attendre avant de pouvoir retenter le test';
                $result['prochaine_disponible'] = $prochaineAutorisee->toDateTimeString();
                return response()->json(['success' => true, 'data' => $result]);
            }
        }
        
        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }
}