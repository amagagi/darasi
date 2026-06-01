<?php
// app/Models/AbonnementType.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * MODÈLE TYPE D'ABONNEMENT
 * 
 * Définit une formule d'abonnement (ex: "Mensuel Gestion Projet")
 * 
 * @property int $id
 * @property string $nom
 * @property string|null $description
 * @property int $duree_jours
 * @property float $prix
 * @property int|null $nb_cours_max
 * @property bool $est_populaire
 * @property bool $est_actif
 * @property int $ordre
 * @property int|null $categorie_id
 * @property-read Categorie|null $categorie
 * @property-read \Illuminate\Database\Eloquent\Collection|AbonnementSouscrit[] $souscriptions
 * @property-read \Illuminate\Database\Eloquent\Collection|Cours[] $cours
 */
class AbonnementType extends Model
{
    protected $table = 'abonnements_types';
    
    protected $fillable = [
        'nom', 'description', 'duree_jours', 'prix',
        'nb_cours_max', 'est_populaire', 'est_actif', 'ordre',
        'categorie_id'  // ← AJOUTER CETTE LIGNE
    ];

    protected $casts = [
        'est_populaire' => 'boolean',
        'est_actif' => 'boolean',
    ];

    /**
     * Catégorie associée à cette formule d'abonnement
     */
    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }

    /**
     * Souscriptions actives pour cette formule
     */
    public function souscriptions()
    {
        return $this->hasMany(AbonnementSouscrit::class, 'type_abonnement_id');
    }

    /**
     * Cours inclus dans cet abonnement
     * (tous les cours de la catégorie, sauf exceptions)
     */
    public function cours()
    {
        return $this->belongsToMany(Cours::class, 'abonnements_cours', 'abonnement_type_id', 'cours_id');
    }

    /**
     * Paiements associés
     */
    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'abonnement_type_id');
    }

    /**
     * Vérifier si la formule est disponible
     */
    public function estDisponible()
    {
        return $this->est_actif && $this->categorie && $this->categorie->is_active;
    }
}