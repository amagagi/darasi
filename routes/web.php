<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\WebAuthController;
use App\Models\Notification;

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

// ⚠️ AJOUTE CETTE NOUVELLE ROUTE juste en dessous :
Route::post('/password/reset', function (Illuminate\Http\Request $request) {
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:8|confirmed',
    ]);

    // On cherche l'utilisateur avec cet email
    $user = \App\Models\User::where('email', $request->email)->first();

    if (!$user) {
        return back()->withErrors(['email' => 'Utilisateur non trouvé.']);
    }

    // On met à jour le mot de passe
    $user->password = bcrypt($request->password);
    $user->save();

    // On redirige vers la page de login Admin avec un message de succès
    return redirect('/admin/login')->with('status', 'Mot de passe réinitialisé avec succès !');
})->name('password.update');


Route::post('/admin/notifications/{id}/mark-read', function ($id) {
    $notification = Notification::find($id);
    if ($notification) {
        $notification->update(['est_lu' => true]);
    }
    return response()->json(['success' => true]);
})->middleware(['auth'])->name('notifications.mark-read');
