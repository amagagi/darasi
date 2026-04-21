<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\WebAuthController;

// Routes web (navigateur)
Route::get('/', function () {
    return view('welcome');
});

Route::post('/login', [WebAuthController::class, 'login'])->name('login');
Route::post('/register', [WebAuthController::class, 'register'])->name('register');
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout')->middleware('auth');
