<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CoursController;
use App\Http\Controllers\Api\PoleController;
use App\Http\Controllers\Api\DemandeController;
use App\Http\Controllers\Api\InscriptionController;
use App\Http\Controllers\Api\ModuleController;
use App\Http\Controllers\Api\LeconController;
use App\Http\Controllers\Api\ApprenantController;

// Routes publiques (pas besoin d'authentification)
Route::post("/register", [AuthController::class, "register"]);
Route::post("/login", [AuthController::class, "login"]);

Route::get("/cours", [CoursController::class, "index"]);
Route::get("/cours/{id}", [CoursController::class, "show"]);
Route::get("/poles", [PoleController::class, "index"]);
Route::get("/poles/{id}/cours", [PoleController::class, "cours"]);
Route::post("/demandes-formation", [DemandeController::class, "store"]);

// Routes protégées (nécessitent authentification)
Route::middleware("auth:sanctum")->group(function () {
    // Auth
    Route::post("/logout", [AuthController::class, "logout"]);
    Route::get("/me", [AuthController::class, "me"]);
    Route::put("/profile", [AuthController::class, "updateProfile"]);
    
    // Cours
    Route::get("/cours/{id}/contenu", [CoursController::class, "contenu"]);
    
    // Inscriptions
    Route::post('/inscription/{cours_id}', [InscriptionController::class, 'store']);
    Route::get('/mes-inscriptions', [InscriptionController::class, 'mesInscriptions']);
    Route::get('/verifier-inscription/{cours_id}', [InscriptionController::class, 'verifierInscription']);
    
    // Modules et Leçons
    Route::get('/cours/{cours_id}/modules', [ModuleController::class, 'index']);
    Route::get('/modules/{id}', [ModuleController::class, 'show']);
    Route::get('/lecons/{id}/contenu', [LeconController::class, 'contenu']);
    Route::post('/lecons/{id}/complete', [LeconController::class, 'marquerComplete']);

// Apprenant
Route::prefix('apprenant')->group(function () {
    Route::get('/dashboard', [ApprenantController::class, 'dashboard']);
    Route::get('/progression/{cours_id}', [ApprenantController::class, 'progressionCours']);
    Route::post('/messages/envoyer', [ApprenantController::class, 'envoyerMessage']);
    Route::get('/messages', [ApprenantController::class, 'mesMessages']);
    Route::post('/messages/{id}/lire', [ApprenantController::class, 'marquerMessageLu']);
    Route::get('/notifications', [ApprenantController::class, 'mesNotifications']);
    Route::post('/notifications/{id}/lire', [ApprenantController::class, 'marquerNotificationLue']);
});
});