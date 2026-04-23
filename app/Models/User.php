<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nom', 'prenom', 'email', 'telephone', 'password', 'role', 'avatar'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Relations
    public function coursCommeFormateur()
    {
        return $this->hasMany(Cours::class, 'formateur_id');
    }

    public function inscriptions()
    {
        return $this->hasMany(Inscription::class, 'apprenant_id');
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'apprenant_id');
    }

    public function abonnements()
    {
        return $this->hasMany(AbonnementSouscrit::class, 'apprenant_id');
    }

    public function discussions()
    {
        return $this->hasMany(ForumDiscussion::class, 'apprenant_id');
    }

    public function reponsesForum()
    {
        return $this->hasMany(ForumReponse::class, 'formateur_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // Vérification des rôles
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isFormateur()
    {
        return $this->role === 'formateur';
    }

    public function isApprenant()
    {
        return $this->role === 'apprenant';
    }

    public function cours()
    {
        return $this->hasMany(Cours::class, 'formateur_id');
    }

        /**
     * Récupérer les certificats de l'utilisateur via ses inscriptions
     */
    public function certificats()
    {
        return $this->hasManyThrough(
            Certificat::class,        // Table cible
            Inscription::class,        // Table intermédiaire
            'apprenant_id',            // Clé étrangère dans inscriptions (vers users)
            'inscription_id',          // Clé étrangère dans certificats (vers inscriptions)
            'id',                      // Clé locale dans users
            'id'                       // Clé locale dans inscriptions
        );
    }
}