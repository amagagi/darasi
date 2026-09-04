<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CoursController;
use App\Http\Controllers\Api\PoleController;
use App\Http\Controllers\Api\DemandeController;
use App\Http\Controllers\Api\PaiementController;
use App\Http\Controllers\Api\InscriptionController;
use App\Http\Controllers\Api\AbonnementController;
use App\Http\Controllers\Api\ModuleController;
use App\Http\Controllers\Api\LeconController;
use App\Http\Controllers\Api\LeconMediaController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\ApprenantController;
use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\FormateurController;
use App\Http\Controllers\Api\ApprenantAbonnementController;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\TestFinalController;
use App\Http\Controllers\Api\CertificatController;
use App\Http\Controllers\Api\AnnonceController;
use App\Http\Controllers\Api\ContenuSiteController;
use App\Http\Controllers\Api\PartnerController;
use App\Http\Controllers\Api\SiteStatisticController;
use App\Http\Controllers\Api\PlatformController;
use App\Http\Controllers\Api\SiteVisitController;
use App\Http\Controllers\Api\TestimonialController;

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
// ATTENTION : la documentation KomiPay ne décrit AUCUNE notification
// serveur-à-serveur. Le seul champ de rappel documenté est `url_retour`, propre
// à l'endpoint `generate-payment-gateway` (page de paiement hébergée) que nous
// n'utilisons pas. Cette route ne sera donc en pratique jamais appelée.
// La confirmation des paiements repose entièrement sur l'interrogation de
// `check-transaction-status` : sondage depuis l'application et commande
// planifiée `komipay:sync`. Route conservée au cas où KomiPay ajouterait des
// webhooks — elle revérifie de toute façon le statut auprès d'eux.
Route::post('/webhooks/komipay', [PaiementController::class, 'webhook']);

// ✅ Certificats - Vérification publique (pas besoin d'auth)
Route::get('/certificats/verify/{code}', [CertificatController::class, 'verify']);

// Annonces / actualités (vitrine + bandeau d'alerte)
// Routes publiques : un jeton Sanctum, s'il est présent, élargit simplement
// l'audience visible (cf. AnnonceController::cibleDepuis).
Route::get('/annonces', [AnnonceController::class, 'index']);
Route::get('/annonces/banniere', [AnnonceController::class, 'banniere']);
Route::get('/annonces/{id}', [AnnonceController::class, 'show'])->whereNumber('id');

// Contenus éditoriaux de la vitrine (vision, mission, valeurs...)
Route::get('/contenus-site', [ContenuSiteController::class, 'index']);

// Diffusion des médias de leçon (PDF, vidéo).
// Hors auth:sanctum à dessein : c'est la SIGNATURE de l'URL qui autorise
// l'accès. Un lecteur vidéo ou PDF côté navigateur charge l'URL lui-même et ne
// peut pas joindre d'en-tête Authorization. L'URL est délivrée pour 30 minutes
// par GET /api/lecons/{id}/media, après contrôle de l'inscription au cours.
Route::get('/lecons/{lecon}/stream/{type}', [LeconMediaController::class, 'stream'])
    ->middleware('signed')
    ->whereNumber('lecon')
    ->whereIn('type', ['pdf', 'video'])
    ->name('lecons.media.stream');

// Partenaires et chiffres clés (section « Ils nous font confiance »)
Route::get('/partners', [PartnerController::class, 'index']);
Route::get('/site-statistics', [SiteStatisticController::class, 'index']);

// Nos applications / plateformes
Route::get('/platforms', [PlatformController::class, 'index']);
Route::get('/platforms/{slug}', [PlatformController::class, 'show']);

// Témoignages (« Ce que disent nos apprenants »)
Route::get('/testimonials', [TestimonialController::class, 'index']);

// Compteur de visites — endpoint dédié plutôt qu'un middleware global (le
// frontend est une SPA, une seule page peut déclencher plusieurs appels API).
Route::post('/site-visits', [SiteVisitController::class, 'store'])->middleware('track.visit');
Route::get('/site-visits/summary', [SiteVisitController::class, 'summary']);

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
    // Renouvelle l'URL signée d'un média (vidéos longues dont l'URL expire).
    // Paramètre optionnel ?type=pdf|video ; par défaut le type de la leçon.
    Route::get('/lecons/{id}/media', [LeconMediaController::class, 'refresh']);

    // ── Messagerie formateur ↔ apprenants ────────────────────────────────
    // Mise à jour par interrogation périodique : `?since=ID` ne renvoie que les
    // messages postérieurs, ce qui rend le sondage peu coûteux et évite
    // d'ajouter un service WebSocket au déploiement.
    Route::prefix('conversations')->group(function () {
        Route::get('/', [ConversationController::class, 'index']);
        Route::get('/non-lus', [ConversationController::class, 'nonLus']);

        // Ouvre ou récupère un fil. `groupe` : tous les inscrits du cours.
        // `prive` : formateur ↔ un apprenant (corps : apprenant_id).
        Route::post('/cours/{cours}/groupe', [ConversationController::class, 'groupe']);
        Route::post('/cours/{cours}/prive', [ConversationController::class, 'prive']);

        Route::get('/{conversation}/messages', [ConversationController::class, 'messages']);
        Route::post('/{conversation}/messages', [ConversationController::class, 'envoyer']);
        Route::post('/{conversation}/lu', [ConversationController::class, 'marquerCommeLu']);
    });
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
