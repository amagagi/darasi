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

        // Dans app/Models/User.php, ajoute cette méthode :

    public function autorisationsCorrection()
    {
        return $this->hasMany(AutorisationCorrection::class, 'formateur_id');
    }

        /**
         * Vérifier si l'utilisateur a un abonnement actif pour une catégorie
         * 
         * @param int $categorieId
         * @return bool
         */
        public function aAbonnementActifPourCategorie($categorieId)
        {
            return $this->abonnements()
                ->where('categorie_id', $categorieId)
                ->where('statut', 'actif')
                ->where('date_fin', '>', now())
                ->exists();
        }

        /**
         * Vérifier si l'utilisateur peut accéder à un cours
         * 
         * @param Cours $cours
         * @return bool
         */
        public function peutAccederCours($cours)
        {
            // 1. Cours gratuit
            if ($cours->est_gratuit) return true;
            
            // 2. Achat individuel (via inscription payante)
            if ($this->inscriptions()->where('cours_id', $cours->id)->exists()) return true;
            
            // 3. Abonnement actif pour la catégorie du cours
            if ($this->aAbonnementActifPourCategorie($cours->categorie_id)) return true;
            
            return false;
        }

        /**
         * Récupérer les catégories pour lesquelles l'utilisateur a un abonnement actif
         * 
         * @return \Illuminate\Support\Collection
         */
        public function categoriesAvecAbonnementActif()
        {
            return $this->abonnements()
                ->where('statut', 'actif')
                ->where('date_fin', '>', now())
                ->with('categorie')
                ->get()
                ->pluck('categorie')
                ->filter();
        }
}