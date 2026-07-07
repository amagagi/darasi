<?php
// app/Http/Controllers/Api/TestFinalController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TestFinal;
use App\Models\Inscription;
use App\Models\Module;
use App\Models\ConfigTentative;
use App\Models\TentativeTestFinal;
use App\Models\TentativeTest;        // ✅ AJOUTER CETTE LIGNE
use App\Models\ReponseQuestion;
use App\Models\Question;
use App\Models\ChoixQuestion;
use App\Models\Notification;
use App\Models\Certificat;
use App\Models\Cours;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * CONTROLLER DES TESTS FINAUX DE COURS
 * 
 * @description Gère l'accès aux tests finaux, la soumission des réponses et la génération de certificats
 * @author amagagi
 * @version 1.0
 */
class TestFinalController extends Controller
{
    // 1. getQuestions($testFinalId) - Récupérer les questions du test final
        /**
     * Récupérer les questions d'un test final
     * 
     * @method GET
     * @endpoint /api/tests/final/{testFinalId}/questions
     * @requires Auth (Bearer Token)
     * 
     * @url_param int testFinalId required - ID du test final
     * 
     * Vérifications :
     * - Inscription au cours
     * - Tous les modules du cours sont validés
     * - Nombre de tentatives (max 2)
     * - Délai entre tentatives (48h)
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "test_final": {
     *       "id": 1,
     *       "titre": "Test final - Laravel",
     *       "duree_limite": 45,
     *       "note_minimale": 14.0
     *     },
     *     "tentative_numero": 1,
     *     "max_tentatives": 2,
     *     "questions": [...]
     *   }
     * }
     * 
     * @response 403 {
     *   "error": "Vous devez valider tous les modules avant de passer le test final",
     *   "modules_non_valides": 2
     * }
     */
    public function getQuestions($testFinalId)
    {
        $user = auth()->user();
        
        // Récupérer le test final avec son cours
        $testFinal = TestFinal::with(['cours'])->findOrFail($testFinalId);
        $cours = $testFinal->cours;
        
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
        
        // 2. Vérifier que tous les modules sont validés
        // Récupérer tous les modules du cours
        $modules = Module::where('cours_id', $cours->id)->get();
        $modulesNonValides = [];
        $modulesValides = 0;
        
        // Vérifier chaque module
        foreach ($modules as $module) {
            $testModule = $module->test;
            if ($testModule) {
                // Vérifier si l'apprenant a validé ce module
                $tentativeValide = TentativeTest::where('inscription_id', $inscription->id)
                    ->where('test_id', $testModule->id)
                    ->where('est_valide', true)
                    ->exists();
                
                if (!$tentativeValide) {
                    $modulesNonValides[] = $module->titre;
                } else {
                    $modulesValides++;
                }
            } else {
                // Si le module n'a pas de test, il est considéré comme validé
                // (à adapter selon votre logique)
            }
        }
        
        if (!empty($modulesNonValides)) {
            return response()->json([
                'error' => 'Vous devez valider tous les modules avant de passer le test final',
                'modules_non_valides' => $modulesNonValides,
                'modules_valides' => $modulesValides,
                'total_modules' => $modules->count()
            ], 403);
        }
        
        // 3. Vérifier les tentatives
        $config = ConfigTentative::where('test_final_id', $testFinalId)->first();
        $maxTentatives = $config ? $config->max_tentatives : 2;
        $delaiHeures = $config ? $config->delai_heures : 48;
        
        $tentativesCount = TentativeTestFinal::where('inscription_id', $inscription->id)
            ->where('test_final_id', $testFinalId)
            ->count();
        
        if ($tentativesCount >= $maxTentatives) {
            return response()->json([
                'error' => 'Vous avez atteint le nombre maximum de tentatives',
                'max_tentatives' => $maxTentatives
            ], 403);
        }
        
        // 4. Vérifier le délai entre les tentatives
        if ($tentativesCount > 0) {
            $derniereTentative = TentativeTestFinal::where('inscription_id', $inscription->id)
                ->where('test_final_id', $testFinalId)
                ->latest()
                ->first();
            
            $prochaineAutorisee = $derniereTentative->date_tentative->addHours($delaiHeures);
            if (now()->lt($prochaineAutorisee)) {
                return response()->json([
                    'error' => 'Vous devez attendre avant de pouvoir retenter le test final',
                    'prochaine_disponible' => $prochaineAutorisee->toDateTimeString()
                ], 403);
            }
        }
        
        // 5. Récupérer les questions avec leurs choix (sans est_correct)
        $questions = Question::where('test_final_id', $testFinalId)
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
                'test_final' => [
                    'id' => $testFinal->id,
                    'titre' => $testFinal->titre,
                    'duree_limite' => $testFinal->duree_limite,
                    'note_minimale' => $testFinal->note_minimale / 5 // Convertir 70/100 en 14/20
                ],
                'tentative_numero' => $tentativesCount + 1,
                'max_tentatives' => $maxTentatives,
                'questions' => $questions
            ]
        ]);
    }
    // 2. submit(Request $request, $testFinalId) - Soumettre les réponses
        /**
     * Soumettre les réponses du test final
     * 
     * @method POST
     * @endpoint /api/tests/final/{testFinalId}/submit
     * @requires Auth (Bearer Token)
     * 
     * @url_param int testFinalId required - ID du test final
     * 
     * @body_param array reponses required - Liste des réponses
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "Test final soumis avec succès",
     *   "data": {
     *     "tentative_id": 1,
     *     "note": 15.5,
     *     "est_reussi": true,
     *     "note_minimale": 14.0,
     *     "certificat_genere": true,
     *     "certificat_code": "CERT-ABCD1234"
     *   }
     * }
     */
    public function submit(Request $request, $testFinalId)
    {
        $user = auth()->user();
        
        // Validation
        $request->validate([
            'reponses' => 'required|array',
            'reponses.*.question_id' => 'required|exists:questions,id',
            'reponses.*.choix_id' => 'nullable|exists:choix_questions,id',
            'reponses.*.reponse_texte' => 'nullable|string'
        ]);
        
        // Récupérer le test final avec son cours
        $testFinal = TestFinal::with(['cours'])->findOrFail($testFinalId);
        $cours = $testFinal->cours;
        
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
        
        // 2. Vérifier que tous les modules sont validés
        $modules = Module::where('cours_id', $cours->id)->get();
        foreach ($modules as $module) {
            $testModule = $module->test;
            if ($testModule) {
                $tentativeValide = TentativeTest::where('inscription_id', $inscription->id)
                    ->where('test_id', $testModule->id)
                    ->where('est_valide', true)
                    ->exists();
                
                if (!$tentativeValide) {
                    return response()->json([
                        'error' => 'Vous devez valider tous les modules avant de passer le test final'
                    ], 403);
                }
            }
        }
        
        // 3. Vérifier les tentatives
        $config = ConfigTentative::where('test_final_id', $testFinalId)->first();
        $maxTentatives = $config ? $config->max_tentatives : 2;
        $delaiHeures = $config ? $config->delai_heures : 48;
        
        $tentativesCount = TentativeTestFinal::where('inscription_id', $inscription->id)
            ->where('test_final_id', $testFinalId)
            ->count();
        
        if ($tentativesCount >= $maxTentatives) {
            return response()->json([
                'error' => 'Vous avez atteint le nombre maximum de tentatives'
            ], 403);
        }
        
        // 4. Vérifier le délai
        if ($tentativesCount > 0) {
            $derniereTentative = TentativeTestFinal::where('inscription_id', $inscription->id)
                ->where('test_final_id', $testFinalId)
                ->latest()
                ->first();
            
            $prochaineAutorisee = $derniereTentative->date_tentative->addHours($delaiHeures);
            if (now()->lt($prochaineAutorisee)) {
                return response()->json([
                    'error' => 'Vous devez attendre avant de pouvoir retenter le test final'
                ], 403);
            }
        }
        
        DB::beginTransaction();
        
        try {
            // 5. Créer la tentative
            $tentative = TentativeTestFinal::create([
                'inscription_id' => $inscription->id,
                'test_final_id' => $testFinalId,
                'tentative_numero' => $tentativesCount + 1,
                'date_tentative' => now(),
                'note' => null,
                'est_reussi' => false,
                'a_obtenu_certificat' => false
            ]);
            
            $totalPoints = 0;
            $pointsObtenus = 0;
            $aQuestionsOuvertes = false;
            
            // 6. Traiter chaque réponse
            foreach ($request->reponses as $reponseData) {
                $question = Question::findOrFail($reponseData['question_id']);
                $totalPoints += $question->points;
                
                $reponse = ReponseQuestion::create([
                    'tentative_final_id' => $tentative->id,
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
                
                if ($question->type === 'ouverte') {
                    $aQuestionsOuvertes = true;
                }
            }
            
            // 7. Calculer la note sur 20
            $note = $totalPoints > 0 ? round(($pointsObtenus / $totalPoints) * 20, 2) : 0;
            $noteMinimale = $testFinal->note_minimale / 5; // Convertir 70/100 en 14/20
            $estReussi = $note >= $noteMinimale;
            
            // 8. Mettre à jour la tentative
            $tentative->update([
                'note' => $note,
                'est_reussi' => $estReussi
            ]);
            
            // 9. Si réussi, générer le certificat
            $certificat = null;
            if ($estReussi) {
                $codeVerification = 'CERT-' . strtoupper(uniqid()) . '-' . substr(md5($user->id . $cours->id . now()), 0, 6);
                
                $certificat = Certificat::create([
                    'inscription_id' => $inscription->id,
                    'tentative_final_id' => $tentative->id,
                    'code_verification' => $codeVerification,
                    'url_pdf' => null,
                    'date_emission' => now(),
                    'est_valide' => 1
                ]);
                
                $tentative->update([
                    'a_obtenu_certificat' => true,
                    'date_obtention_certificat' => now()
                ]);
                
                // Mettre à jour l'inscription
                $inscription->update([
                    'statut' => 'termine',
                    'date_completion' => now()
                ]);
            }
            
            // 10. Notifier le formateur s'il y a des questions ouvertes
            if ($aQuestionsOuvertes) {
                $formateur = User::find($cours->formateur_id);
                if ($formateur) {
                    Notification::create([
                        'user_id' => $formateur->id,
                        'titre' => 'Questions ouvertes en attente de correction - Test final',
                        'message' => "{$user->prenom} {$user->nom} a soumis des réponses ouvertes pour le test final '{$testFinal->titre}' du cours '{$cours->titre}'.",
                        'type' => 'systeme',
                        'data' => json_encode([
                            'type' => 'correction_finale',
                            'tentative_id' => $tentative->id,
                            'cours_id' => $cours->id,
                            'test_final_id' => $testFinalId
                        ])
                    ]);
                }
            }
            
            DB::commit();
            
            // 11. Construire la réponse
            $response = [
                'tentative_id' => $tentative->id,
                'note' => $note,
                'est_reussi' => $estReussi,
                'note_minimale' => $noteMinimale,
                'points_obtenus' => $pointsObtenus,
                'total_points' => $totalPoints
            ];
            
            if ($certificat) {
                $response['certificat_genere'] = true;
                $response['certificat_code'] = $certificat->code_verification;
            }
            
            if ($aQuestionsOuvertes) {
                $response['a_questions_ouvertes'] = true;
                $response['message_ouvertes'] = 'Certaines questions sont en attente de correction. Le certificat sera généré après correction.';
            }
            
            return response()->json([
                'success' => true,
                'message' => $estReussi ? 'Félicitations ! Test final réussi !' : 'Test final non réussi. Réessayez après le délai.',
                'data' => $response
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Erreur lors de la soumission du test final',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    // 3. getTentativeResults($tentativeId) - Voir les résultats
        /**
     * Récupérer les résultats détaillés d'une tentative de test final
     * 
     * @method GET
     * @endpoint /api/tests/final/tentatives/{tentativeId}/results
     * @requires Auth (Bearer Token)
     * 
     * @url_param int tentativeId required - ID de la tentative
     */
    public function getTentativeResults($tentativeId)
    {
        $user = auth()->user();
        
        $tentative = TentativeTestFinal::with(['inscription', 'reponses.question.choix'])
            ->findOrFail($tentativeId);
        
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
                $detail['reponse_texte'] = $reponse->reponse_texte;
                $correction = $reponse->correction;
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
        
        // Récupérer le certificat si existant
        $certificat = Certificat::where('tentative_final_id', $tentative->id)->first();
        
        return response()->json([
            'success' => true,
            'data' => [
                'tentative_id' => $tentative->id,
                'tentative_numero' => $tentative->tentative_numero,
                'date_tentative' => $tentative->date_tentative->toDateTimeString(),
                'note' => $tentative->note,
                'est_reussi' => $tentative->est_reussi,
                'a_obtenu_certificat' => $tentative->a_obtenu_certificat,
                'certificat' => $certificat ? [
                    'id' => $certificat->id,
                    'code' => $certificat->code_verification,
                    'date_emission' => $certificat->date_emission->toDateTimeString(),
                    'est_valide' => $certificat->est_valide
                ] : null,
                'total_points' => $totalPoints,
                'points_obtenus' => $pointsObtenus,
                'reponses' => $reponsesDetail
            ]
        ]);
    }

    // 4. checkAccess($testFinalId) - Vérifier l'accès au test final
        /**
     * Vérifier si l'apprenant peut accéder au test final
     * 
     * @method GET
     * @endpoint /api/tests/final/{testFinalId}/accessible
     * @requires Auth (Bearer Token)
     */
    public function checkAccess($testFinalId)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['success' => false, 'error' => 'Utilisateur non authentifié'], 401);
        }
        
        $testFinal = TestFinal::with(['cours'])->findOrFail($testFinalId);
        $cours = $testFinal->cours;
        
        $result = [
            'accessible' => true,
            'reason' => null,
            'modules_non_valides' => [],
            'tentatives_restantes' => 0,
            'prochaine_disponible' => null,
            'tous_modules_valides' => false
        ];
        
        $inscription = Inscription::where('apprenant_id', $user->id)
            ->where('cours_id', $cours->id)
            ->where('statut', 'actif')
            ->first();
        
        if (!$inscription) {
            $result['accessible'] = false;
            $result['reason'] = 'Vous devez être inscrit à ce cours';
            return response()->json(['success' => true, 'data' => $result]);
        }
        
        // Vérifier les modules validés
        $modules = Module::where('cours_id', $cours->id)->get();
        $modulesNonValides = [];
        
        foreach ($modules as $module) {
            $testModule = $module->test;
            if ($testModule) {
                $tentativeValide = TentativeTest::where('inscription_id', $inscription->id)
                    ->where('test_id', $testModule->id)
                    ->where('est_valide', true)
                    ->exists();
                
                if (!$tentativeValide) {
                    $modulesNonValides[] = $module->titre;
                }
            }
        }
        
        if (!empty($modulesNonValides)) {
            $result['accessible'] = false;
            $result['reason'] = 'Vous devez valider tous les modules avant de passer le test final';
            $result['modules_non_valides'] = $modulesNonValides;
            $result['tous_modules_valides'] = false;
            return response()->json(['success' => true, 'data' => $result]);
        }
        
        $result['tous_modules_valides'] = true;
        
        // Vérifier les tentatives
        $config = ConfigTentative::where('test_final_id', $testFinalId)->first();
        $maxTentatives = $config ? $config->max_tentatives : 2;
        $delaiHeures = $config ? $config->delai_heures : 48;
        
        $tentativesCount = TentativeTestFinal::where('inscription_id', $inscription->id)
            ->where('test_final_id', $testFinalId)
            ->count();
        
        $result['tentatives_restantes'] = $maxTentatives - $tentativesCount;
        
        if ($tentativesCount >= $maxTentatives) {
            $result['accessible'] = false;
            $result['reason'] = 'Vous avez atteint le nombre maximum de tentatives';
            return response()->json(['success' => true, 'data' => $result]);
        }
        
        if ($tentativesCount > 0) {
            $derniereTentative = TentativeTestFinal::where('inscription_id', $inscription->id)
                ->where('test_final_id', $testFinalId)
                ->latest()
                ->first();
            
            $prochaineAutorisee = $derniereTentative->date_tentative->addHours($delaiHeures);
            if (now()->lt($prochaineAutorisee)) {
                $result['accessible'] = false;
                $result['reason'] = 'Vous devez attendre avant de pouvoir retenter le test final';
                $result['prochaine_disponible'] = $prochaineAutorisee->toDateTimeString();
                return response()->json(['success' => true, 'data' => $result]);
            }
        }
        
        return response()->json(['success' => true, 'data' => $result]);
    }

}