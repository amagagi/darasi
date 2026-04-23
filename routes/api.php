<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CoursController;
use App\Http\Controllers\Api\PoleController;
use App\Http\Controllers\Api\DemandeController;
use App\Http\Controllers\Api\InscriptionController;
use App\Http\Controllers\Api\ModuleController;
use App\Http\Controllers\Api\LeconController;

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
});