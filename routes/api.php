<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CoursController;
use App\Http\Controllers\Api\PoleController;
use App\Http\Controllers\Api\DemandeController;

Route::post("/register", [AuthController::class, "register"]);
Route::post("/login", [AuthController::class, "login"]);

Route::get("/cours", [CoursController::class, "index"]);
Route::get("/cours/{id}", [CoursController::class, "show"]);
Route::get("/poles", [PoleController::class, "index"]);
Route::get("/poles/{id}/cours", [PoleController::class, "cours"]);
Route::post("/demandes-formation", [DemandeController::class, "store"]);

Route::middleware("auth:sanctum")->group(function () {
    Route::post("/logout", [AuthController::class, "logout"]);
    Route::get("/me", [AuthController::class, "me"]);
    Route::put("/profile", [AuthController::class, "updateProfile"]);
    Route::get("/cours/{id}/contenu", [CoursController::class, "contenu"]);
});
