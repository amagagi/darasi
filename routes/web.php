<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\WebAuthController;

// Routes web (navigateur)
Route::get('/', function () {
    return view('welcome');
});

// Changer le préfixe des routes web pour éviter conflit avec API
Route::post('/web/login', [WebAuthController::class, 'login'])->name('web.login');
Route::post('/web/register', [WebAuthController::class, 'register'])->name('web.register');
Route::post('/web/logout', [WebAuthController::class, 'logout'])->name('web.logout')->middleware('auth');

// Route pour la réinitialisation de mot de passe
Route::get('/password/reset/{token}', function ($token) {
    return view('auth.reset-password', ['token' => $token]);
})->name('password.reset');