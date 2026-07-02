<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nom', 'prenom', 'email', 'telephone', 'password', 'role', 'avatar',
        'is_active', 'deactivated_reason'  // Ajout des nouveaux champs
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'deactivated_at' => 'datetime',
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

    public function getNameAttribute(): string
    {
        return trim(($this->nom ?? '') . ' ' . ($this->prenom ?? '')) ?: 'Utilisateur';
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->nom . ' ' . $this->prenom);
    }

    protected function password(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => bcrypt($value),
        );
    }

    /**
     * Récupérer les certificats de l'utilisateur via ses inscriptions
     */
    public function certificats()
    {
        return $this->hasManyThrough(
            Certificat::class,
            Inscription::class,
            'apprenant_id',
            'inscription_id',
            'id',
            'id'
        );
    }

    public function autorisationsCorrection()
    {
        return $this->hasMany(AutorisationCorrection::class, 'formateur_id');
    }

    /**
     * Vérifier si l'utilisateur a un abonnement actif pour une catégorie
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

    // ============================================
    // GESTION DES COMPTES (ACTIVATION/DÉSACTIVATION)
    // ============================================

    /**
     * Vérifier si l'utilisateur peut se connecter
     */
    public function canLogin(): bool
    {
        // Formateur : doit être validé ET actif
        if ($this->role === 'formateur') {
            return $this->email_verified_at !== null && $this->is_active;
        }
        
        // Apprenant et Admin : juste besoin d'être actif
        return $this->is_active;
    }

    /**
     * Vérifier si l'utilisateur a besoin d'une validation admin
     */
    public function needsValidation(): bool
    {
        return $this->role === 'formateur' && $this->email_verified_at === null;
    }

    /**
     * Valider un formateur par l'admin
     */
    public function validateByAdmin(): void
    {
        $this->update([
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Désactiver un compte
     */
    public function deactivate(string $reason = null): void
    {
        $this->update([
            'is_active' => false,
            'deactivated_at' => now(),
            'deactivated_reason' => $reason,
        ]);
    }

    /**
     * Activer un compte
     */
    public function activate(): void
    {
        $this->update([
            'is_active' => true,
            'deactivated_at' => null,
            'deactivated_reason' => null,
        ]);
    }

    /**
     * Vérifier si le compte est désactivé
     */
    public function isDeactivated(): bool
    {
        return !$this->is_active;
    }
}