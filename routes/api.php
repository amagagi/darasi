<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CoursController;
use App\Http\Controllers\Api\PoleController;
use App\Http\Controllers\Api\DemandeController;
use App\Http\Controllers\Api\PaiementController;
use App\Http\Controllers\Api\InscriptionController;
use App\Http\Controllers\Api\ModuleController;
use App\Http\Controllers\Api\LeconController;
use App\Http\Controllers\Api\ApprenantController;
use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\FormateurController;
use App\Http\Controllers\Api\ApprenantAbonnementController;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\TestFinalController;
use App\Http\Controllers\Api\CertificatController;

// ============================================
// ROUTES PUBLIQUES
// ============================================

// Authentification
Route::post('/register', [AuthController::class, 'register'])->middleware('recaptcha');
Route::post('/login', [AuthController::class, 'login'])->middleware('recaptcha');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');

// Cours (vitrine)
Route::get("/cours", [CoursController::class, "index"]);
Route::get("/cours/{id}", [CoursController::class, "show"]);

// Pôles
Route::get("/poles", [PoleController::class, "index"]);
Route::get("/poles/{id}/cours", [PoleController::class, "cours"]);

// Demandes de formation
Route::post("/demandes-formation", [DemandeController::class, "store"]);

// Webhook (public)
Route::post('/webhooks/komipay', [PaiementController::class, 'webhook']);

// ✅ Certificats - Vérification publique (pas besoin d'auth)
Route::get('/certificats/verify/{code}', [CertificatController::class, 'verify']);

// ============================================
// ROUTES PROTÉGÉES (auth:sanctum)
// ============================================

Route::middleware("auth:sanctum")->group(function () {
    
    // ==========================================
    // AUTHENTIFICATION
    // ==========================================
    Route::post("/logout", [AuthController::class, "logout"]);
    Route::get("/me", [AuthController::class, "me"]);
    Route::put("/profile", [AuthController::class, "updateProfile"]);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/email/resend', [AuthController::class, 'resendVerification']);
    
    // ==========================================
    // COURS
    // ==========================================
    Route::get("/cours/{id}/contenu", [CoursController::class, "contenu"]);
    
    // ==========================================
    // INSCRIPTIONS
    // ==========================================
    Route::post('/inscription/{cours_id}', [InscriptionController::class, 'store']);
    Route::get('/mes-inscriptions', [InscriptionController::class, 'mesInscriptions']);
    Route::get('/verifier-inscription/{cours_id}', [InscriptionController::class, 'verifierInscription']);
    
    // ==========================================
    // MODULES ET LEÇONS
    // ==========================================
    Route::get('/cours/{cours_id}/modules', [ModuleController::class, 'index']);
    Route::get('/modules/{id}', [ModuleController::class, 'show']);
    Route::get('/lecons/{id}/contenu', [LeconController::class, 'contenu']);
    Route::post('/lecons/{id}/complete', [LeconController::class, 'marquerComplete']);
    
    // ==========================================
    // TESTS DE MODULE
    // ==========================================
    Route::prefix('tests')->group(function () {
        Route::get('{testId}/questions', [TestController::class, 'getQuestions']);
        Route::post('{testId}/submit', [TestController::class, 'submit']);
        Route::get('tentatives/{tentativeId}/results', [TestController::class, 'getTentativeResults']);
        Route::get('{testId}/accessible', [TestController::class, 'checkAccess']);
    });
    
    // ==========================================
    // TESTS FINAUX
    // ==========================================
    Route::prefix('tests/final')->group(function () {
        Route::get('{testFinalId}/questions', [TestFinalController::class, 'getQuestions']);
        Route::post('{testFinalId}/submit', [TestFinalController::class, 'submit']);
        Route::get('tentatives/{tentativeId}/results', [TestFinalController::class, 'getTentativeResults']);
        Route::get('{testFinalId}/accessible', [TestFinalController::class, 'checkAccess']);
    });
    
    // ==========================================
    // CERTIFICATS (protégés)
    // ==========================================
    Route::prefix('certificats')->group(function () {
        Route::get('/', [CertificatController::class, 'index']);
        Route::get('{id}', [CertificatController::class, 'show']);
        Route::get('{id}/pdf', [CertificatController::class, 'downloadPdf']);
    });
    
    // ==========================================
    // PAIEMENTS
    // ==========================================
    Route::prefix('paiement')->group(function () {
        Route::post('/initier', [PaiementController::class, 'initier']);
        Route::get('/statut/{transaction_id}', [PaiementController::class, 'statut']);
    });
    
    // ==========================================
    // APPRENANT
    // ==========================================
    Route::prefix('apprenant')->group(function () {
        Route::get('/dashboard', [ApprenantController::class, 'dashboard']);
        Route::get('/progression/{cours_id}', [ApprenantController::class, 'progressionCours']);
        Route::post('/messages/envoyer', [ApprenantController::class, 'envoyerMessage']);
        Route::get('/messages', [ApprenantController::class, 'mesMessages']);
        Route::post('/messages/{id}/lire', [ApprenantController::class, 'marquerMessageLu']);
        Route::get('/notifications', [ApprenantController::class, 'mesNotifications']);
        Route::post('/notifications/{id}/lire', [ApprenantController::class, 'marquerNotificationLue']);
    });
    
    // ==========================================
    // ABONNEMENTS
    // ==========================================
    Route::prefix('abonnements')->group(function () {
        Route::get('/', [ApprenantAbonnementController::class, 'index']);
        Route::get('/categorie/{categorie_id}', [ApprenantAbonnementController::class, 'parCategorie']);
        Route::post('/souscrire', [ApprenantAbonnementController::class, 'souscrire']);
        Route::get('/mes-abonnements', [ApprenantAbonnementController::class, 'mesAbonnements']);
        Route::get('/verifier/{cours_id}', [ApprenantAbonnementController::class, 'verifierAcces']);
        Route::post('/annuler/{id}', [ApprenantAbonnementController::class, 'annuler']);
        Route::get('/statut/{transaction_id}', [ApprenantAbonnementController::class, 'statutPaiement']);
    });
    
    // ==========================================
    // FORMATEUR
    // ==========================================
    Route::prefix('formateur')->group(function () {
        Route::get('/dashboard', [FormateurController::class, 'dashboard']);
        Route::get('/stats', [FormateurController::class, 'statistiques']);
        Route::get('/cours', [FormateurController::class, 'mesCours']);
        Route::get('/cours/{id}', [FormateurController::class, 'showCours']);
        Route::get('/cours/{id}/apprenants', [FormateurController::class, 'apprenantsCours']);
        Route::get('/cours/{id}/questions', [FormateurController::class, 'questionsCours']);
        Route::post('/questions/{id}/repondre', [FormateurController::class, 'repondreQuestion']);
        Route::put('/questions/{id}/resoudre', [FormateurController::class, 'resoudreQuestion']);
        Route::get('/tentatives/{quiz_id}', [FormateurController::class, 'tentativesQuiz']);
        Route::post('/correction/{reponse_id}', [FormateurController::class, 'corrigerQuestion']);
    });
    
    // ==========================================
    // ADMIN
    // ==========================================
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        
        // Gestion des utilisateurs
        Route::get('/users', [AdminController::class, 'listUsers']);
        Route::get('/users/{id}', [AdminController::class, 'showUser']);
        Route::post('/users', [AdminController::class, 'createUser']);
        Route::put('/users/{id}', [AdminController::class, 'updateUser']);
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
        
        // Gestion des cours
        Route::get('/cours', [AdminController::class, 'listCours']);
        Route::get('/cours/{id}', [AdminController::class, 'showCours']);
        Route::post('/cours', [AdminController::class, 'createCours']);
        Route::put('/cours/{id}', [AdminController::class, 'updateCours']);
        Route::delete('/cours/{id}', [AdminController::class, 'deleteCours']);
        Route::put('/cours/{id}/publier', [AdminController::class, 'publierCours']);
        Route::put('/cours/{id}/archiver', [AdminController::class, 'archiverCours']);
        
        // Gestion des formateurs
        Route::get('/formateurs', [AdminController::class, 'listFormateurs']);
        Route::get('/formateurs/{id}', [AdminController::class, 'showFormateur']);
        Route::post('/formateurs/{id}/autoriser', [AdminController::class, 'autoriserCorrection']);
        Route::delete('/formateurs/{id}/autoriser', [AdminController::class, 'revoquerAutorisation']);
        Route::get('/formateurs/{id}/cours', [AdminController::class, 'formateurCours']);
        
        // Gestion des demandes de formation
        Route::get('/demandes', [AdminController::class, 'listDemandes']);
        Route::get('/demandes/{id}', [AdminController::class, 'showDemande']);
        Route::put('/demandes/{id}/traiter', [AdminController::class, 'traiterDemande']);
        Route::put('/demandes/{id}/realiser', [AdminController::class, 'realiserDemande']);
        Route::put('/demandes/{id}/rejeter', [AdminController::class, 'rejeterDemande']);
        
        // Gestion des abonnements
        Route::get('/abonnements', [AdminController::class, 'listAbonnements']);
        Route::get('/abonnements/{id}', [AdminController::class, 'showAbonnement']);
        Route::post('/abonnements', [AdminController::class, 'createAbonnement']);
        Route::put('/abonnements/{id}', [AdminController::class, 'updateAbonnement']);
        Route::delete('/abonnements/{id}', [AdminController::class, 'deleteAbonnement']);
        Route::put('/abonnements/{id}/toggle', [AdminController::class, 'toggleAbonnement']);
        Route::get('/categories', [AdminController::class, 'listCategories']);
        
        // Statistiques avancées
        Route::get('/stats/ventes', [AdminController::class, 'ventesParMois']);
        Route::get('/stats/cours-populaires', [AdminController::class, 'coursPopulaires']);
        Route::get('/stats/inscriptions-recentes', [AdminController::class, 'inscriptionsRecentes']);
        
        // Révocation de certificats
        Route::post('/certificats/{id}/revoke', [CertificatController::class, 'revoke']);
    });
});