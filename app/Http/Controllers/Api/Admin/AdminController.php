<?php
// app/Http/Controllers/Api/Admin/AdminController.php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Cours;
use App\Models\Inscription;
use App\Models\Paiement;
use App\Models\AutorisationCorrection;
use App\Models\DemandesFormation;
use App\Models\Module;
use App\Models\Lecon;
use Carbon\Carbon;
use App\Models\AbonnementType;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    /**
     * Vérifier que l'utilisateur est admin
     */
    private function checkAdmin()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Accès non autorisé');
        }
    }

    /**
     * Dashboard Admin
     */
    public function dashboard()
    {
        $this->checkAdmin();
        
        $totalUsers = User::count();
        $totalApprenants = User::where('role', 'apprenant')->count();
        $totalFormateurs = User::where('role', 'formateur')->count();
        $totalCours = Cours::count();
        $coursPublies = Cours::where('statut', 'publie')->count();
        $coursBrouillon = Cours::where('statut', 'brouillon')->count();
        $totalInscriptions = Inscription::count();
        $ca = Paiement::where('statut', 'paye')->sum('montant');
        $demandesEnAttente = DemandesFormation::where('statut', 'en_attente')->count();
        
        return response()->json([
            'success' => true,
            'data' => [
                'utilisateurs' => [
                    'total' => $totalUsers,
                    'apprenants' => $totalApprenants,
                    'formateurs' => $totalFormateurs,
                ],
                'cours' => [
                    'total' => $totalCours,
                    'publies' => $coursPublies,
                    'brouillon' => $coursBrouillon,
                ],
                'inscriptions' => $totalInscriptions,
                'chiffre_affaires' => $ca,
                'demandes_en_attente' => $demandesEnAttente,
            ]
        ]);
    }

    /**
     * Liste des utilisateurs
     * 
     * @method GET
     * @endpoint /api/admin/users
     */
    public function listUsers(Request $request)
    {
        $this->checkAdmin();
        
        $query = User::query();
        
        // Filtre par rôle
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }
        
        // Recherche par nom ou email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $users = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Voir un utilisateur
     * 
     * @method GET
     * @endpoint /api/admin/users/{id}
     */
    public function showUser($id)
    {
        $this->checkAdmin();
        
        $user = User::with(['inscriptions.cours', 'paiements'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Créer un utilisateur (formateur uniquement)
     * 
     * @method POST
     * @endpoint /api/admin/users
     */
    public function createUser(Request $request)
    {
        $this->checkAdmin();
        
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'telephone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'role' => 'required|in:formateur,admin',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Utilisateur créé avec succès',
            'data' => $user
        ], 201);
    }

    /**
     * Modifier un utilisateur
     * 
     * @method PUT
     * @endpoint /api/admin/users/{id}
     */
    public function updateUser(Request $request, $id)
    {
        $this->checkAdmin();
        
        $user = User::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'nom' => 'sometimes|string|max:100',
            'prenom' => 'sometimes|string|max:100',
            'telephone' => 'nullable|string|max:20',
            'role' => 'sometimes|in:apprenant,formateur,admin',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $user->update($request->only(['nom', 'prenom', 'telephone', 'role']));
        
        return response()->json([
            'success' => true,
            'message' => 'Utilisateur modifié avec succès',
            'data' => $user
        ]);
    }

    /**
     * Supprimer un utilisateur
     * 
     * @method DELETE
     * @endpoint /api/admin/users/{id}
     */
        public function deleteUser($id)
    {
        $this->checkAdmin();
        
        $user = User::findOrFail($id);
        
        // Empêcher la suppression de son propre compte
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas supprimer votre propre compte'
            ], 400);
        }
        
        // Supprimer les tokens d'abord
        $user->tokens()->delete();
        
        // Supprimer l'utilisateur
        $user->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Utilisateur supprimé avec succès'
        ]);
    }

    // ========== GESTION DES COURS ==========

    /**
     * Liste des cours (admin)
     * 
     * @method GET
     * @endpoint /api/admin/cours
     */
    public function listCours(Request $request)
    {
        $this->checkAdmin();
        
        $query = Cours::with(['pole', 'formateur']);
        
        // Filtre par statut
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }
        
        // Filtre par pôle
        if ($request->has('pole_id')) {
            $query->where('pole_id', $request->pole_id);
        }
        
        // Recherche par titre
        if ($request->has('search')) {
            $query->where('titre', 'like', "%{$request->search}%");
        }
        
        $cours = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return response()->json([
            'success' => true,
            'data' => $cours
        ]);
    }

    /**
     * Voir un cours (admin)
     * 
     * @method GET
     * @endpoint /api/admin/cours/{id}
     */
    public function showCours($id)
    {
        $this->checkAdmin();
        
        $cours = Cours::with(['pole', 'formateur', 'modules.lecons'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $cours
        ]);
    }

    /**
     * Créer un cours
     * 
     * @method POST
     * @endpoint /api/admin/cours
     */
    public function createCours(Request $request)
    {
        $this->checkAdmin();
        
        $validator = Validator::make($request->all(), [
            'titre' => 'required|string|max:200',
            'description' => 'nullable|string',
            'objectifs' => 'nullable|string',
            'prerequis' => 'nullable|string',
            'pole_id' => 'required|exists:poles,id',
            'formateur_id' => 'required|exists:users,id',
            'prix' => 'nullable|numeric|min:0',
            'est_gratuit' => 'boolean',
            'est_certifiant' => 'boolean',
            'image_couverture' => 'nullable|string|max:255',
            'video_presentation' => 'nullable|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $cours = Cours::create([
            'titre' => $request->titre,
            'description' => $request->description,
            'objectifs' => $request->objectifs,
            'prerequis' => $request->prerequis,
            'pole_id' => $request->pole_id,
            'formateur_id' => $request->formateur_id,
            'prix' => $request->prix ?? 0,
            'est_gratuit' => $request->est_gratuit ?? false,
            'est_certifiant' => $request->est_certifiant ?? false,
            'image_couverture' => $request->image_couverture,
            'video_presentation' => $request->video_presentation,
            'statut' => 'brouillon',
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Cours créé avec succès',
            'data' => $cours
        ], 201);
    }

    /**
     * Modifier un cours
     * 
     * @method PUT
     * @endpoint /api/admin/cours/{id}
     */
    public function updateCours(Request $request, $id)
    {
        $this->checkAdmin();
        
        $cours = Cours::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'titre' => 'sometimes|string|max:200',
            'description' => 'nullable|string',
            'objectifs' => 'nullable|string',
            'prerequis' => 'nullable|string',
            'pole_id' => 'sometimes|exists:poles,id',
            'formateur_id' => 'sometimes|exists:users,id',
            'prix' => 'nullable|numeric|min:0',
            'est_gratuit' => 'boolean',
            'est_certifiant' => 'boolean',
            'statut' => 'sometimes|in:brouillon,publie,archive',
            'image_couverture' => 'nullable|string|max:255',
            'video_presentation' => 'nullable|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $cours->update($request->only([
            'titre', 'description', 'objectifs', 'prerequis',
            'pole_id', 'formateur_id', 'prix', 'est_gratuit',
            'est_certifiant', 'statut', 'image_couverture', 'video_presentation'
        ]));
        
        if ($request->statut === 'publie' && !$cours->published_at) {
            $cours->update(['published_at' => now()]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Cours modifié avec succès',
            'data' => $cours
        ]);
    }

    /**
     * Supprimer un cours
     * 
     * @method DELETE
     * @endpoint /api/admin/cours/{id}
     */
    public function deleteCours($id)
    {
        $this->checkAdmin();
        
        $cours = Cours::findOrFail($id);
        $cours->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Cours supprimé avec succès'
        ]);
    }

    /**
     * Publier un cours
     * 
     * @method PUT
     * @endpoint /api/admin/cours/{id}/publier
     */
    public function publierCours($id)
    {
        $this->checkAdmin();
        
        $cours = Cours::findOrFail($id);
        $cours->update([
            'statut' => 'publie',
            'published_at' => now()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Cours publié avec succès'
        ]);
    }

    /**
     * Archiver un cours
     * 
     * @method PUT
     * @endpoint /api/admin/cours/{id}/archiver
     */
    public function archiverCours($id)
    {
        $this->checkAdmin();
        
        $cours = Cours::findOrFail($id);
        $cours->update(['statut' => 'archive']);
        
        return response()->json([
            'success' => true,
            'message' => 'Cours archivé avec succès'
        ]);
    }

    // ========== GESTION DES FORMATEURS ==========

    /**
     * Liste des formateurs
     * 
     * @method GET
     * @endpoint /api/admin/formateurs
     */
    public function listFormateurs(Request $request)
    {
        $this->checkAdmin();
        
        $query = User::where('role', 'formateur');
        
        // Recherche par nom
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                ->orWhere('prenom', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $formateurs = $query->with(['autorisationsCorrection.cours'])->paginate(20);
        
        return response()->json([
            'success' => true,
            'data' => $formateurs
        ]);
    }

    /**
     * Voir un formateur
     * 
     * @method GET
     * @endpoint /api/admin/formateurs/{id}
     */
    public function showFormateur($id)
    {
        $this->checkAdmin();
        
        $formateur = User::where('role', 'formateur')
            ->with(['autorisationsCorrection.cours', 'cours'])
            ->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $formateur
        ]);
    }

    /**
     * Autoriser un formateur à corriger un cours spécifique
     * 
     * @method POST
     * @endpoint /api/admin/formateurs/{id}/autoriser
     */
    public function autoriserCorrection(Request $request, $id)
    {
        $this->checkAdmin();
        
        $validator = Validator::make($request->all(), [
            'cours_id' => 'required|exists:cours,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $formateur = User::where('role', 'formateur')->findOrFail($id);
        
        // Vérifier si l'autorisation existe déjà
        $existant = AutorisationCorrection::where('formateur_id', $id)
            ->where('cours_id', $request->cours_id)
            ->first();
        
        if ($existant) {
            return response()->json([
                'success' => false,
                'message' => 'Cette autorisation existe déjà'
            ], 400);
        }
        
        $autorisation = AutorisationCorrection::create([
            'formateur_id' => $id,
            'cours_id' => $request->cours_id,
            'autorise_par' => auth()->id(),
            'est_active' => true,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Formateur autorisé à corriger ce cours',
            'data' => $autorisation
        ], 201);
    }

    /**
     * Révoquer l'autorisation d'un formateur
     * 
     * @method DELETE
     * @endpoint /api/admin/formateurs/{id}/autoriser
     */
    public function revoquerAutorisation(Request $request, $id)
    {
        $this->checkAdmin();
        
        $validator = Validator::make($request->all(), [
            'cours_id' => 'required|exists:cours,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $autorisation = AutorisationCorrection::where('formateur_id', $id)
            ->where('cours_id', $request->cours_id)
            ->firstOrFail();
        
        $autorisation->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Autorisation révoquée'
        ]);
    }

    /**
     * Cours assignés à un formateur
     * 
     * @method GET
     * @endpoint /api/admin/formateurs/{id}/cours
     */
    public function formateurCours($id)
    {
        $this->checkAdmin();
        
        $formateur = User::where('role', 'formateur')->findOrFail($id);
        
        $cours = Cours::where('formateur_id', $id)->get();
        
        return response()->json([
            'success' => true,
            'data' => $cours
        ]);
    }

    // ========== GESTION DES DEMANDES DE FORMATION ==========

    /**
     * Liste des demandes de formation
     * 
     * @method GET
     * @endpoint /api/admin/demandes
     */
    public function listDemandes(Request $request)
    {
        $this->checkAdmin();
        
        $query = DemandesFormation::query();
        
        // Filtre par statut
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }
        
        // Recherche par nom, email, titre
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('titre_cours_souhaite', 'like', "%{$search}%");
            });
        }
        
        $demandes = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return response()->json([
            'success' => true,
            'data' => $demandes
        ]);
    }

    /**
     * Voir une demande
     * 
     * @method GET
     * @endpoint /api/admin/demandes/{id}
     */
    public function showDemande($id)
    {
        $this->checkAdmin();
        
        $demande = DemandesFormation::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $demande
        ]);
    }

    /**
     * Traiter une demande (prise en compte)
     * 
     * @method PUT
     * @endpoint /api/admin/demandes/{id}/traiter
     */
    public function traiterDemande($id)
    {
        $this->checkAdmin();
        
        $demande = DemandesFormation::findOrFail($id);
        $demande->update([
            'statut' => 'prise_en_compte',
            'traite_le' => now(),
            'traite_par' => auth()->id()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Demande prise en compte'
        ]);
    }

    /**
     * Réaliser une demande (cours créé)
     * 
     * @method PUT
     * @endpoint /api/admin/demandes/{id}/realiser
     */
    public function realiserDemande($id)
    {
        $this->checkAdmin();
        
        $demande = DemandesFormation::findOrFail($id);
        $demande->update([
            'statut' => 'realise',
            'traite_le' => now(),
            'traite_par' => auth()->id()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Demande réalisée avec succès'
        ]);
    }

    /**
     * Rejeter une demande
     * 
     * @method PUT
     * @endpoint /api/admin/demandes/{id}/rejeter
     */
    public function rejeterDemande(Request $request, $id)
    {
        $this->checkAdmin();
        
        $validator = Validator::make($request->all(), [
            'motif' => 'required|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $demande = DemandesFormation::findOrFail($id);
        $demande->update([
            'statut' => 'rejete',
            'traite_le' => now(),
            'traite_par' => auth()->id(),
            'commentaire_admin' => $request->motif
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Demande rejetée'
        ]);
    }

    // ========== GESTION DES ABONNEMENTS ==========

    /**
     * Liste des abonnements
     * 
     * @method GET
     * @endpoint /api/admin/abonnements
     */
    public function listAbonnements(Request $request)
    {
        $this->checkAdmin();
        
        $query = AbonnementType::query();
        
        // Filtre par statut actif/inactif
        if ($request->has('est_actif')) {
            $query->where('est_actif', $request->est_actif);
        }
        
        $abonnements = $query->orderBy('ordre')->get();
        
        return response()->json([
            'success' => true,
            'data' => $abonnements
        ]);
    }

    /**
     * Voir un abonnement
     * 
     * @method GET
     * @endpoint /api/admin/abonnements/{id}
     */
    public function showAbonnement($id)
    {
        $this->checkAdmin();
        
        $abonnement = AbonnementType::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $abonnement
        ]);
    }

    /**
     * Créer un abonnement
     * 
     * @method POST
     * @endpoint /api/admin/abonnements
     */
    public function createAbonnement(Request $request)
    {
        $this->checkAdmin();
        
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:100',
            'description' => 'nullable|string',
            'duree_jours' => 'required|integer|min:1',
            'prix' => 'required|numeric|min:0',
            'nb_cours_max' => 'nullable|integer',
            'est_populaire' => 'boolean',
            'ordre' => 'integer',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $abonnement = AbonnementType::create([
            'nom' => $request->nom,
            'description' => $request->description,
            'duree_jours' => $request->duree_jours,
            'prix' => $request->prix,
            'nb_cours_max' => $request->nb_cours_max,
            'est_populaire' => $request->est_populaire ?? false,
            'est_actif' => true,
            'ordre' => $request->ordre ?? 0,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Abonnement créé avec succès',
            'data' => $abonnement
        ], 201);
    }

    /**
     * Modifier un abonnement
     * 
     * @method PUT
     * @endpoint /api/admin/abonnements/{id}
     */
    public function updateAbonnement(Request $request, $id)
    {
        $this->checkAdmin();
        
        $abonnement = AbonnementType::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'nom' => 'sometimes|string|max:100',
            'description' => 'nullable|string',
            'duree_jours' => 'sometimes|integer|min:1',
            'prix' => 'sometimes|numeric|min:0',
            'nb_cours_max' => 'nullable|integer',
            'est_populaire' => 'boolean',
            'est_actif' => 'boolean',
            'ordre' => 'integer',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $abonnement->update($request->only([
            'nom', 'description', 'duree_jours', 'prix',
            'nb_cours_max', 'est_populaire', 'est_actif', 'ordre'
        ]));
        
        return response()->json([
            'success' => true,
            'message' => 'Abonnement modifié avec succès',
            'data' => $abonnement
        ]);
    }

    /**
     * Supprimer un abonnement
     * 
     * @method DELETE
     * @endpoint /api/admin/abonnements/{id}
     */
    public function deleteAbonnement($id)
    {
        $this->checkAdmin();
        
        $abonnement = AbonnementType::findOrFail($id);
        $abonnement->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Abonnement supprimé avec succès'
        ]);
    }

    /**
     * Activer/Désactiver un abonnement
     * 
     * @method PUT
     * @endpoint /api/admin/abonnements/{id}/toggle
     */
    public function toggleAbonnement($id)
    {
        $this->checkAdmin();
        
        $abonnement = AbonnementType::findOrFail($id);
        $abonnement->update(['est_actif' => !$abonnement->est_actif]);
        
        return response()->json([
            'success' => true,
            'message' => $abonnement->est_actif ? 'Abonnement activé' : 'Abonnement désactivé',
            'data' => $abonnement
        ]);
    }

    // ========== STATISTIQUES AVANCÉES ==========


    /**
     * Ventes par mois (chiffre d'affaires)
     * 
     * @method GET
     * @endpoint /api/admin/stats/ventes
     */
    public function ventesParMois(Request $request)
    {
        $this->checkAdmin();
        
        $mois = $request->get('mois', 12); // Nombre de mois à afficher
        
        $ventes = collect();
        
        for ($i = $mois - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $moisNom = $date->locale('fr')->format('F Y');
            
            $total = Paiement::where('statut', 'paye')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('montant');
            
            $ventes->push([
                'mois' => $moisNom,
                'total' => (int) $total,
                'annee' => $date->year,
                'mois_numero' => $date->month
            ]);
        }
        
        return response()->json([
            'success' => true,
            'data' => $ventes
        ]);
    }

    /**
     * Cours les plus populaires (top 10)
     * 
     * @method GET
     * @endpoint /api/admin/stats/cours-populaires
     */
    public function coursPopulaires(Request $request)
    {
        $this->checkAdmin();
        
        $limit = $request->get('limit', 10);
        
        $cours = Cours::withCount('inscriptions')
            ->with(['formateur', 'pole'])
            ->orderBy('inscriptions_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($c) {
                return [
                    'id' => $c->id,
                    'titre' => $c->titre,
                    'prix' => $c->prix,
                    'nb_inscriptions' => $c->inscriptions_count,
                    'chiffre_affaires' => $c->inscriptions_count * $c->prix,
                    'formateur' => $c->formateur ? $c->formateur->nom . ' ' . $c->formateur->prenom : null,
                    'pole' => $c->pole ? $c->pole->nom : null
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => $cours
        ]);
    }

    /**
     * Inscriptions récentes (30 derniers jours)
     * 
     * @method GET
     * @endpoint /api/admin/stats/inscriptions-recentes
     */
    public function inscriptionsRecentes(Request $request)
    {
        $this->checkAdmin();
        
        $jours = $request->get('jours', 30);
        
        $inscriptions = Inscription::with(['apprenant', 'cours'])
            ->where('created_at', '>=', Carbon::now()->subDays($jours))
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function($i) {
                return [
                    'id' => $i->id,
                    'apprenant' => $i->apprenant ? $i->apprenant->nom . ' ' . $i->apprenant->prenom : null,
                    'cours' => $i->cours ? $i->cours->titre : null,
                    'date' => $i->created_at->format('Y-m-d H:i:s'),
                    'progression' => $i->progression,
                    'statut' => $i->statut
                ];
            });
        
        $totalJours = Inscription::where('created_at', '>=', Carbon::now()->subDays($jours))->count();
        
        return response()->json([
            'success' => true,
            'total' => $totalJours,
            'data' => $inscriptions
        ]);
    }
}