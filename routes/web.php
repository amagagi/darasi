<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;

// Routes web (navigateur)
Route::get('/', function () {
    return view('welcome');
});

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');
