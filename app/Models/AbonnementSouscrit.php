<?php
// app/Models/AbonnementSouscrit.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * MODÈLE ABONNEMENT SOUSCRIT
 * 
 * Représente un abonnement acheté par un apprenant
 * 
 * @property int $id
 * @property int $apprenant_id
 * @property int $type_abonnement_id
 * @property int $categorie_id
 * @property Carbon $date_debut
 * @property Carbon $date_fin
 * @property string $statut (actif/expire/annule/suspendu)
 * @property int|null $paiement_id
 * @property-read User $apprenant
 * @property-read AbonnementType $type
 * @property-read Categorie $categorie
 */
class AbonnementSouscrit extends Model
{
    protected $table = 'abonnements_souscrits';
    protected $fillable = [
        'apprenant_id', 'type_abonnement_id', 'categorie_id',  // ← AJOUTER categorie_id
        'date_debut', 'date_fin', 'statut', 'paiement_id'
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
    ];

    /**
     * L'apprenant qui a souscrit
     */
    public function apprenant()
    {
        return $this->belongsTo(User::class, 'apprenant_id');
    }


        // La relation s'appelle 'type' mais dans le Resource on utilise 'typeAbonnement'
    public function typeAbonnement()
    {
        return $this->belongsTo(AbonnementType::class, 'type_abonnement_id');
    }

    public function type()
    {
        return $this->belongsTo(AbonnementType::class, 'type_abonnement_id');
    }

    /**
     * La catégorie concernée par l'abonnement
     */
    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }

    /**
     * Le paiement associé
     */
    public function paiement()
    {
        return $this->belongsTo(Paiement::class);
    }

    /**
     * Inscriptions liées à cet abonnement
     */
    public function inscriptions()
    {
        return $this->hasMany(Inscription::class);
    }

    /**
     * Vérifier si l'abonnement est actif
     * 
     * @return bool
     */
    public function isActif()
    {
        return $this->statut === 'actif' && Carbon::now()->lessThanOrEqualTo($this->date_fin);
    }

    /**
     * Vérifier si l'abonnement expire bientôt (dans 7 jours)
     * 
     * @return bool
     */
    public function expireBientot()
    {
        if (!$this->isActif()) return false;
        return Carbon::now()->diffInDays($this->date_fin) <= 7;
    }

    /**
     * Jours restants avant expiration
     * 
     * @return int
     */
    public function joursRestants()
    {
        if (!$this->isActif()) return 0;
        return Carbon::now()->diffInDays($this->date_fin, false);
    }

    /**
     * Vérifier si un cours est accessible via cet abonnement
     * 
     * @param int $coursId
     * @return bool
     */
    public function peutAccederCours($coursId)
    {
        if (!$this->isActif()) return false;
        
        // Vérifier si le cours appartient à la catégorie de l'abonnement.
        // categorie_id = null : abonnement "général", couvre toutes les
        // catégories (voir le formulaire AbonnementTypeResource).
        $cours = Cours::find($coursId);
        return $cours && ($this->categorie_id === null || $cours->categorie_id === $this->categorie_id);
    }
}
