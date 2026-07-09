<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. NETTOYER LES DOUBLONS
        $this->cleanAbonnementsTypes();
        $this->cleanCours();
        $this->cleanModules();
        $this->cleanLecons();
        $this->cleanNiveaux();
        $this->cleanDemandesFormation();

        // 2. AJOUTER LES CLÉS ÉTRANGÈRES
        $this->addForeignKeys();

        // 3. AJOUTER LES INDEX MANQUANTS
        $this->addMissingIndexes();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer les clés étrangères
        $this->dropForeignKeys();
    }

    /**
     * Nettoyer les doublons dans abonnements_types
     */
    private function cleanAbonnementsTypes(): void
    {
        $duplicates = DB::table('abonnements_types')
            ->select('nom', 'duree_jours', 'prix', DB::raw('MIN(id) as keep_id'))
            ->groupBy('nom', 'duree_jours', 'prix')
            ->having(DB::raw('COUNT(*)'), '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            $toDelete = DB::table('abonnements_types')
                ->where('nom', $dup->nom)
                ->where('duree_jours', $dup->duree_jours)
                ->where('prix', $dup->prix)
                ->where('id', '!=', $dup->keep_id)
                ->pluck('id');

            foreach ($toDelete as $oldId) {
                DB::table('abonnements_souscrits')
                    ->where('type_abonnement_id', $oldId)
                    ->update(['type_abonnement_id' => $dup->keep_id]);
            }

            DB::table('abonnements_types')
                ->whereIn('id', $toDelete)
                ->delete();
        }
    }

    /**
     * Nettoyer les doublons dans cours
     */
    private function cleanCours(): void
    {
        $duplicates = DB::table('cours')
            ->select('titre', 'description', DB::raw('MIN(id) as keep_id'))
            ->groupBy('titre', 'description')
            ->having(DB::raw('COUNT(*)'), '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            $toDelete = DB::table('cours')
                ->where('titre', $dup->titre)
                ->where('description', $dup->description)
                ->where('id', '!=', $dup->keep_id)
                ->pluck('id');

            foreach ($toDelete as $oldId) {
                DB::table('inscriptions')
                    ->where('cours_id', $oldId)
                    ->update(['cours_id' => $dup->keep_id]);
            }

            foreach ($toDelete as $oldId) {
                DB::table('tests_finaux')
                    ->where('cours_id', $oldId)
                    ->update(['cours_id' => $dup->keep_id]);
            }

            DB::table('cours')
                ->whereIn('id', $toDelete)
                ->delete();
        }
    }

    /**
     * Nettoyer les doublons dans modules
     */
    private function cleanModules(): void
    {
        $duplicates = DB::table('modules')
            ->select('cours_id', 'titre', DB::raw('MIN(id) as keep_id'))
            ->groupBy('cours_id', 'titre')
            ->having(DB::raw('COUNT(*)'), '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            $toDelete = DB::table('modules')
                ->where('cours_id', $dup->cours_id)
                ->where('titre', $dup->titre)
                ->where('id', '!=', $dup->keep_id)
                ->pluck('id');

            foreach ($toDelete as $oldId) {
                DB::table('lecons')
                    ->where('module_id', $oldId)
                    ->update(['module_id' => $dup->keep_id]);
            }

            foreach ($toDelete as $oldId) {
                DB::table('tests')
                    ->where('module_id', $oldId)
                    ->update(['module_id' => $dup->keep_id]);
            }

            DB::table('modules')
                ->whereIn('id', $toDelete)
                ->delete();
        }
    }

    /**
     * Nettoyer les doublons dans lecons
     */
    private function cleanLecons(): void
    {
        $duplicates = DB::table('lecons')
            ->select('module_id', 'titre', DB::raw('MIN(id) as keep_id'))
            ->groupBy('module_id', 'titre')
            ->having(DB::raw('COUNT(*)'), '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            $toDelete = DB::table('lecons')
                ->where('module_id', $dup->module_id)
                ->where('titre', $dup->titre)
                ->where('id', '!=', $dup->keep_id)
                ->pluck('id');

            foreach ($toDelete as $oldId) {
                DB::table('progres_lecons')
                    ->where('lecon_id', $oldId)
                    ->update(['lecon_id' => $dup->keep_id]);
            }

            DB::table('lecons')
                ->whereIn('id', $toDelete)
                ->delete();
        }
    }

    /**
     * Nettoyer les doublons dans niveaux
     */
    private function cleanNiveaux(): void
    {
        $duplicates = DB::table('niveaux')
            ->select('pole_id', 'libelle', DB::raw('MIN(id) as keep_id'))
            ->groupBy('pole_id', 'libelle')
            ->having(DB::raw('COUNT(*)'), '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            $toDelete = DB::table('niveaux')
                ->where('pole_id', $dup->pole_id)
                ->where('libelle', $dup->libelle)
                ->where('id', '!=', $dup->keep_id)
                ->pluck('id');

            foreach ($toDelete as $oldId) {
                DB::table('cours')
                    ->where('niveau_id', $oldId)
                    ->update(['niveau_id' => $dup->keep_id]);
            }

            DB::table('niveaux')
                ->whereIn('id', $toDelete)
                ->delete();
        }
    }

    /**
     * Nettoyer les doublons dans demandes_formation
     */
    private function cleanDemandesFormation(): void
    {
        $duplicates = DB::table('demandes_formation')
            ->select('email', 'titre_cours_souhaite', DB::raw('MIN(id) as keep_id'))
            ->groupBy('email', 'titre_cours_souhaite')
            ->having(DB::raw('COUNT(*)'), '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            $toDelete = DB::table('demandes_formation')
                ->where('email', $dup->email)
                ->where('titre_cours_souhaite', $dup->titre_cours_souhaite)
                ->where('id', '!=', $dup->keep_id)
                ->pluck('id');

            DB::table('demandes_formation')
                ->whereIn('id', $toDelete)
                ->delete();
        }
    }

    /**
     * Ajouter toutes les clés étrangères
     */
    private function addForeignKeys(): void
    {
        Schema::table('abonnements_cours', function (Blueprint $table) {
            $table->foreign('abonnement_type_id')->references('id')->on('abonnements_types')->onDelete('cascade');
            $table->foreign('cours_id')->references('id')->on('cours')->onDelete('cascade');
        });

        Schema::table('abonnements_souscrits', function (Blueprint $table) {
            $table->foreign('apprenant_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('type_abonnement_id')->references('id')->on('abonnements_types')->onDelete('cascade');
            $table->foreign('categorie_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('paiement_id')->references('id')->on('paiements')->onDelete('set null');
        });

        Schema::table('autorisations_correction', function (Blueprint $table) {
            $table->foreign('formateur_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('cours_id')->references('id')->on('cours')->onDelete('cascade');
            $table->foreign('autorise_par')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->foreign('pole_id')->references('id')->on('poles')->onDelete('cascade');
        });

        Schema::table('certificats', function (Blueprint $table) {
            $table->foreign('inscription_id')->references('id')->on('inscriptions')->onDelete('cascade');
            $table->foreign('tentative_final_id')->references('id')->on('tentatives_test_final')->onDelete('cascade');
            $table->foreign('revoque_par')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('choix_questions', function (Blueprint $table) {
            $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
        });

        Schema::table('config_tentatives', function (Blueprint $table) {
            $table->foreign('test_id')->references('id')->on('tests')->onDelete('cascade');
            $table->foreign('test_final_id')->references('id')->on('tests_finaux')->onDelete('cascade');
        });

        Schema::table('contenus_juridiques', function (Blueprint $table) {
            $table->foreign('modifie_par')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('corrections_ouvertes', function (Blueprint $table) {
            $table->foreign('reponse_id')->references('id')->on('reponses_questions')->onDelete('cascade');
            $table->foreign('corrige_par')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('cours', function (Blueprint $table) {
            $table->foreign('pole_id')->references('id')->on('poles')->onDelete('cascade');
            $table->foreign('formateur_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('categorie_id')->references('id')->on('categories')->onDelete('set null');
            $table->foreign('niveau_id')->references('id')->on('niveaux')->onDelete('set null');
        });

        Schema::table('demandes_formation', function (Blueprint $table) {
            $table->foreign('traite_par')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('forum_discussions', function (Blueprint $table) {
            $table->foreign('cours_id')->references('id')->on('cours')->onDelete('cascade');
            $table->foreign('apprenant_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('forum_reponses', function (Blueprint $table) {
            $table->foreign('discussion_id')->references('id')->on('forum_discussions')->onDelete('cascade');
            $table->foreign('formateur_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('inscriptions', function (Blueprint $table) {
            $table->foreign('apprenant_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('cours_id')->references('id')->on('cours')->onDelete('cascade');
            $table->foreign('abonnement_id')->references('id')->on('abonnements_souscrits')->onDelete('set null');
        });

        Schema::table('lecons', function (Blueprint $table) {
            $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->foreign('expediteur_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('destinataire_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('cours_id')->references('id')->on('cours')->onDelete('cascade');
        });

        Schema::table('modules', function (Blueprint $table) {
            $table->foreign('cours_id')->references('id')->on('cours')->onDelete('cascade');
        });

        Schema::table('niveaux', function (Blueprint $table) {
            $table->foreign('pole_id')->references('id')->on('poles')->onDelete('cascade');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('paiements', function (Blueprint $table) {
            $table->foreign('apprenant_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('cours_id')->references('id')->on('cours')->onDelete('set null');
            $table->foreign('abonnement_type_id')->references('id')->on('abonnements_types')->onDelete('set null');
        });

        Schema::table('paiements_logs', function (Blueprint $table) {
            $table->foreign('paiement_id')->references('id')->on('paiements')->onDelete('cascade');
        });

        Schema::table('progres_lecons', function (Blueprint $table) {
            $table->foreign('inscription_id')->references('id')->on('inscriptions')->onDelete('cascade');
            $table->foreign('lecon_id')->references('id')->on('lecons')->onDelete('cascade');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->foreign('test_id')->references('id')->on('tests')->onDelete('cascade');
            $table->foreign('test_final_id')->references('id')->on('tests_finaux')->onDelete('cascade');
        });

        Schema::table('reponses_questions', function (Blueprint $table) {
            $table->foreign('tentative_test_id')->references('id')->on('tentatives_tests')->onDelete('cascade');
            $table->foreign('tentative_final_id')->references('id')->on('tentatives_test_final')->onDelete('cascade');
            $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
            $table->foreign('choix_id')->references('id')->on('choix_questions')->onDelete('set null');
        });

        Schema::table('tentatives_tests', function (Blueprint $table) {
            $table->foreign('inscription_id')->references('id')->on('inscriptions')->onDelete('cascade');
            $table->foreign('test_id')->references('id')->on('tests')->onDelete('cascade');
        });

        Schema::table('tentatives_test_final', function (Blueprint $table) {
            $table->foreign('inscription_id')->references('id')->on('inscriptions')->onDelete('cascade');
            $table->foreign('test_final_id')->references('id')->on('tests_finaux')->onDelete('cascade');
        });

        Schema::table('tests', function (Blueprint $table) {
            $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');
        });

        Schema::table('tests_finaux', function (Blueprint $table) {
            $table->foreign('cours_id')->references('id')->on('cours')->onDelete('cascade');
        });
    }

    /**
     * Ajouter des index supplémentaires pour optimiser les performances
     */
    private function addMissingIndexes(): void
    {
        Schema::table('cours', function (Blueprint $table) {
            $table->index('titre');
            $table->index('statut');
            $table->index('published_at');
            $table->index(['statut', 'published_at']);
        });

        Schema::table('inscriptions', function (Blueprint $table) {
            $table->index(['cours_id', 'statut']);
            $table->index('date_debut');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->index(['destinataire_id', 'created_at']);
            $table->index(['expediteur_id', 'created_at']);
        });

        Schema::table('paiements', function (Blueprint $table) {
            $table->index('statut');
            $table->index('date_paiement');
            $table->index(['apprenant_id', 'statut']);
        });

        Schema::table('abonnements_souscrits', function (Blueprint $table) {
            $table->index('date_debut');
            $table->index('date_fin');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->index('type');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index('type');
        });
    }

    /**
     * Supprimer les clés étrangères (rollback)
     */
    private function dropForeignKeys(): void
    {
        Schema::table('abonnements_cours', function (Blueprint $table) {
            $table->dropForeign(['abonnement_type_id']);
            $table->dropForeign(['cours_id']);
        });

        Schema::table('abonnements_souscrits', function (Blueprint $table) {
            $table->dropForeign(['apprenant_id']);
            $table->dropForeign(['type_abonnement_id']);
            $table->dropForeign(['categorie_id']);
            $table->dropForeign(['paiement_id']);
        });

        Schema::table('autorisations_correction', function (Blueprint $table) {
            $table->dropForeign(['formateur_id']);
            $table->dropForeign(['cours_id']);
            $table->dropForeign(['autorise_par']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['pole_id']);
        });

        Schema::table('certificats', function (Blueprint $table) {
            $table->dropForeign(['inscription_id']);
            $table->dropForeign(['tentative_final_id']);
            $table->dropForeign(['revoque_par']);
        });

        Schema::table('choix_questions', function (Blueprint $table) {
            $table->dropForeign(['question_id']);
        });

        Schema::table('config_tentatives', function (Blueprint $table) {
            $table->dropForeign(['test_id']);
            $table->dropForeign(['test_final_id']);
        });

        Schema::table('contenus_juridiques', function (Blueprint $table) {
            $table->dropForeign(['modifie_par']);
        });

        Schema::table('corrections_ouvertes', function (Blueprint $table) {
            $table->dropForeign(['reponse_id']);
            $table->dropForeign(['corrige_par']);
        });

        Schema::table('cours', function (Blueprint $table) {
            $table->dropForeign(['pole_id']);
            $table->dropForeign(['formateur_id']);
            $table->dropForeign(['categorie_id']);
            $table->dropForeign(['niveau_id']);
        });

        Schema::table('demandes_formation', function (Blueprint $table) {
            $table->dropForeign(['traite_par']);
        });

        Schema::table('forum_discussions', function (Blueprint $table) {
            $table->dropForeign(['cours_id']);
            $table->dropForeign(['apprenant_id']);
        });

        Schema::table('forum_reponses', function (Blueprint $table) {
            $table->dropForeign(['discussion_id']);
            $table->dropForeign(['formateur_id']);
        });

        Schema::table('inscriptions', function (Blueprint $table) {
            $table->dropForeign(['apprenant_id']);
            $table->dropForeign(['cours_id']);
            $table->dropForeign(['abonnement_id']);
        });

        Schema::table('lecons', function (Blueprint $table) {
            $table->dropForeign(['module_id']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['expediteur_id']);
            $table->dropForeign(['destinataire_id']);
            $table->dropForeign(['cours_id']);
        });

        Schema::table('modules', function (Blueprint $table) {
            $table->dropForeign(['cours_id']);
        });

        Schema::table('niveaux', function (Blueprint $table) {
            $table->dropForeign(['pole_id']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('paiements', function (Blueprint $table) {
            $table->dropForeign(['apprenant_id']);
            $table->dropForeign(['cours_id']);
            $table->dropForeign(['abonnement_type_id']);
        });

        Schema::table('paiements_logs', function (Blueprint $table) {
            $table->dropForeign(['paiement_id']);
        });

        Schema::table('progres_lecons', function (Blueprint $table) {
            $table->dropForeign(['inscription_id']);
            $table->dropForeign(['lecon_id']);
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['test_id']);
            $table->dropForeign(['test_final_id']);
        });

        Schema::table('reponses_questions', function (Blueprint $table) {
            $table->dropForeign(['tentative_test_id']);
            $table->dropForeign(['tentative_final_id']);
            $table->dropForeign(['question_id']);
            $table->dropForeign(['choix_id']);
        });

        Schema::table('tentatives_tests', function (Blueprint $table) {
            $table->dropForeign(['inscription_id']);
            $table->dropForeign(['test_id']);
        });

        Schema::table('tentatives_test_final', function (Blueprint $table) {
            $table->dropForeign(['inscription_id']);
            $table->dropForeign(['test_final_id']);
        });

        Schema::table('tests', function (Blueprint $table) {
            $table->dropForeign(['module_id']);
        });

        Schema::table('tests_finaux', function (Blueprint $table) {
            $table->dropForeign(['cours_id']);
        });
    }
};