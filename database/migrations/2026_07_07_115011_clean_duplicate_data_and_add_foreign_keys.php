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
        $this->safeTable('abonnements_cours', function (Blueprint $table) {
            $table->foreign('abonnement_type_id')->references('id')->on('abonnements_types')->onDelete('cascade');
            $table->foreign('cours_id')->references('id')->on('cours')->onDelete('cascade');
        });

        $this->safeTable('abonnements_souscrits', function (Blueprint $table) {
            $table->foreign('apprenant_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('type_abonnement_id')->references('id')->on('abonnements_types')->onDelete('cascade');
            $table->foreign('categorie_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('paiement_id')->references('id')->on('paiements')->onDelete('set null');
        });

        $this->safeTable('autorisations_correction', function (Blueprint $table) {
            $table->foreign('formateur_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('cours_id')->references('id')->on('cours')->onDelete('cascade');
            $table->foreign('autorise_par')->references('id')->on('users')->onDelete('cascade');
        });

        $this->safeTable('categories', function (Blueprint $table) {
            $table->foreign('pole_id')->references('id')->on('poles')->onDelete('cascade');
        });

        $this->safeTable('certificats', function (Blueprint $table) {
            $table->foreign('inscription_id')->references('id')->on('inscriptions')->onDelete('cascade');
            $table->foreign('tentative_final_id')->references('id')->on('tentatives_test_final')->onDelete('cascade');
            $table->foreign('revoque_par')->references('id')->on('users')->onDelete('set null');
        });

        $this->safeTable('choix_questions', function (Blueprint $table) {
            $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
        });

        $this->safeTable('config_tentatives', function (Blueprint $table) {
            $table->foreign('test_id')->references('id')->on('tests')->onDelete('cascade');
            $table->foreign('test_final_id')->references('id')->on('tests_finaux')->onDelete('cascade');
        });

        $this->safeTable('contenus_juridiques', function (Blueprint $table) {
            $table->foreign('modifie_par')->references('id')->on('users')->onDelete('set null');
        });

        $this->safeTable('corrections_ouvertes', function (Blueprint $table) {
            $table->foreign('reponse_id')->references('id')->on('reponses_questions')->onDelete('cascade');
            $table->foreign('corrige_par')->references('id')->on('users')->onDelete('cascade');
        });

        $this->safeTable('cours', function (Blueprint $table) {
            $table->foreign('pole_id')->references('id')->on('poles')->onDelete('cascade');
            $table->foreign('formateur_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('categorie_id')->references('id')->on('categories')->onDelete('set null');
            $table->foreign('niveau_id')->references('id')->on('niveaux')->onDelete('set null');
        });

        $this->safeTable('demandes_formation', function (Blueprint $table) {
            $table->foreign('traite_par')->references('id')->on('users')->onDelete('set null');
        });

        $this->safeTable('forum_discussions', function (Blueprint $table) {
            $table->foreign('cours_id')->references('id')->on('cours')->onDelete('cascade');
            $table->foreign('apprenant_id')->references('id')->on('users')->onDelete('cascade');
        });

        $this->safeTable('forum_reponses', function (Blueprint $table) {
            $table->foreign('discussion_id')->references('id')->on('forum_discussions')->onDelete('cascade');
            $table->foreign('formateur_id')->references('id')->on('users')->onDelete('cascade');
        });

        $this->safeTable('inscriptions', function (Blueprint $table) {
            $table->foreign('apprenant_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('cours_id')->references('id')->on('cours')->onDelete('cascade');
            $table->foreign('abonnement_id')->references('id')->on('abonnements_souscrits')->onDelete('set null');
        });

        $this->safeTable('lecons', function (Blueprint $table) {
            $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');
        });

        $this->safeTable('messages', function (Blueprint $table) {
            $table->foreign('expediteur_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('destinataire_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('cours_id')->references('id')->on('cours')->onDelete('cascade');
        });

        $this->safeTable('modules', function (Blueprint $table) {
            $table->foreign('cours_id')->references('id')->on('cours')->onDelete('cascade');
        });

        $this->safeTable('niveaux', function (Blueprint $table) {
            $table->foreign('pole_id')->references('id')->on('poles')->onDelete('cascade');
        });

        $this->safeTable('notifications', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        $this->safeTable('paiements', function (Blueprint $table) {
            $table->foreign('apprenant_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('cours_id')->references('id')->on('cours')->onDelete('set null');
            $table->foreign('abonnement_type_id')->references('id')->on('abonnements_types')->onDelete('set null');
        });

        $this->safeTable('paiements_logs', function (Blueprint $table) {
            $table->foreign('paiement_id')->references('id')->on('paiements')->onDelete('cascade');
        });

        $this->safeTable('progres_lecons', function (Blueprint $table) {
            $table->foreign('inscription_id')->references('id')->on('inscriptions')->onDelete('cascade');
            $table->foreign('lecon_id')->references('id')->on('lecons')->onDelete('cascade');
        });

        $this->safeTable('questions', function (Blueprint $table) {
            $table->foreign('test_id')->references('id')->on('tests')->onDelete('cascade');
            $table->foreign('test_final_id')->references('id')->on('tests_finaux')->onDelete('cascade');
        });

        $this->safeTable('reponses_questions', function (Blueprint $table) {
            $table->foreign('tentative_test_id')->references('id')->on('tentatives_tests')->onDelete('cascade');
            $table->foreign('tentative_final_id')->references('id')->on('tentatives_test_final')->onDelete('cascade');
            $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
            $table->foreign('choix_id')->references('id')->on('choix_questions')->onDelete('set null');
        });

        $this->safeTable('tentatives_tests', function (Blueprint $table) {
            $table->foreign('inscription_id')->references('id')->on('inscriptions')->onDelete('cascade');
            $table->foreign('test_id')->references('id')->on('tests')->onDelete('cascade');
        });

        $this->safeTable('tentatives_test_final', function (Blueprint $table) {
            $table->foreign('inscription_id')->references('id')->on('inscriptions')->onDelete('cascade');
            $table->foreign('test_final_id')->references('id')->on('tests_finaux')->onDelete('cascade');
        });

        $this->safeTable('tests', function (Blueprint $table) {
            $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');
        });

        $this->safeTable('tests_finaux', function (Blueprint $table) {
            $table->foreign('cours_id')->references('id')->on('cours')->onDelete('cascade');
        });
    }

    /**
     * Ajouter des index supplémentaires pour optimiser les performances
     */
    private function addMissingIndexes(): void
    {
        $this->safeTable('cours', function (Blueprint $table) {
            $table->index('titre');
            $table->index('statut');
            $table->index('published_at');
            $table->index(['statut', 'published_at']);
        });

        $this->safeTable('inscriptions', function (Blueprint $table) {
            $table->index(['cours_id', 'statut']);
            $table->index('date_debut');
        });

        $this->safeTable('messages', function (Blueprint $table) {
            $table->index(['destinataire_id', 'created_at']);
            $table->index(['expediteur_id', 'created_at']);
        });

        $this->safeTable('paiements', function (Blueprint $table) {
            $table->index('statut');
            $table->index('date_paiement');
            $table->index(['apprenant_id', 'statut']);
        });

        $this->safeTable('abonnements_souscrits', function (Blueprint $table) {
            $table->index('date_debut');
            $table->index('date_fin');
        });

        $this->safeTable('questions', function (Blueprint $table) {
            $table->index('type');
        });

        $this->safeTable('notifications', function (Blueprint $table) {
            $table->index('type');
        });
    }

    /**
     * Supprimer les clés étrangères (rollback)
     */
    private function dropForeignKeys(): void
    {
        $this->safeTable('abonnements_cours', function (Blueprint $table) {
            $table->dropForeign(['abonnement_type_id']);
            $table->dropForeign(['cours_id']);
        });

        $this->safeTable('abonnements_souscrits', function (Blueprint $table) {
            $table->dropForeign(['apprenant_id']);
            $table->dropForeign(['type_abonnement_id']);
            $table->dropForeign(['categorie_id']);
            $table->dropForeign(['paiement_id']);
        });

        $this->safeTable('autorisations_correction', function (Blueprint $table) {
            $table->dropForeign(['formateur_id']);
            $table->dropForeign(['cours_id']);
            $table->dropForeign(['autorise_par']);
        });

        $this->safeTable('categories', function (Blueprint $table) {
            $table->dropForeign(['pole_id']);
        });

        $this->safeTable('certificats', function (Blueprint $table) {
            $table->dropForeign(['inscription_id']);
            $table->dropForeign(['tentative_final_id']);
            $table->dropForeign(['revoque_par']);
        });

        $this->safeTable('choix_questions', function (Blueprint $table) {
            $table->dropForeign(['question_id']);
        });

        $this->safeTable('config_tentatives', function (Blueprint $table) {
            $table->dropForeign(['test_id']);
            $table->dropForeign(['test_final_id']);
        });

        $this->safeTable('contenus_juridiques', function (Blueprint $table) {
            $table->dropForeign(['modifie_par']);
        });

        $this->safeTable('corrections_ouvertes', function (Blueprint $table) {
            $table->dropForeign(['reponse_id']);
            $table->dropForeign(['corrige_par']);
        });

        $this->safeTable('cours', function (Blueprint $table) {
            $table->dropForeign(['pole_id']);
            $table->dropForeign(['formateur_id']);
            $table->dropForeign(['categorie_id']);
            $table->dropForeign(['niveau_id']);
        });

        $this->safeTable('demandes_formation', function (Blueprint $table) {
            $table->dropForeign(['traite_par']);
        });

        $this->safeTable('forum_discussions', function (Blueprint $table) {
            $table->dropForeign(['cours_id']);
            $table->dropForeign(['apprenant_id']);
        });

        $this->safeTable('forum_reponses', function (Blueprint $table) {
            $table->dropForeign(['discussion_id']);
            $table->dropForeign(['formateur_id']);
        });

        $this->safeTable('inscriptions', function (Blueprint $table) {
            $table->dropForeign(['apprenant_id']);
            $table->dropForeign(['cours_id']);
            $table->dropForeign(['abonnement_id']);
        });

        $this->safeTable('lecons', function (Blueprint $table) {
            $table->dropForeign(['module_id']);
        });

        $this->safeTable('messages', function (Blueprint $table) {
            $table->dropForeign(['expediteur_id']);
            $table->dropForeign(['destinataire_id']);
            $table->dropForeign(['cours_id']);
        });

        $this->safeTable('modules', function (Blueprint $table) {
            $table->dropForeign(['cours_id']);
        });

        $this->safeTable('niveaux', function (Blueprint $table) {
            $table->dropForeign(['pole_id']);
        });

        $this->safeTable('notifications', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        $this->safeTable('paiements', function (Blueprint $table) {
            $table->dropForeign(['apprenant_id']);
            $table->dropForeign(['cours_id']);
            $table->dropForeign(['abonnement_type_id']);
        });

        $this->safeTable('paiements_logs', function (Blueprint $table) {
            $table->dropForeign(['paiement_id']);
        });

        $this->safeTable('progres_lecons', function (Blueprint $table) {
            $table->dropForeign(['inscription_id']);
            $table->dropForeign(['lecon_id']);
        });

        $this->safeTable('questions', function (Blueprint $table) {
            $table->dropForeign(['test_id']);
            $table->dropForeign(['test_final_id']);
        });

        $this->safeTable('reponses_questions', function (Blueprint $table) {
            $table->dropForeign(['tentative_test_id']);
            $table->dropForeign(['tentative_final_id']);
            $table->dropForeign(['question_id']);
            $table->dropForeign(['choix_id']);
        });

        $this->safeTable('tentatives_tests', function (Blueprint $table) {
            $table->dropForeign(['inscription_id']);
            $table->dropForeign(['test_id']);
        });

        $this->safeTable('tentatives_test_final', function (Blueprint $table) {
            $table->dropForeign(['inscription_id']);
            $table->dropForeign(['test_final_id']);
        });

        $this->safeTable('tests', function (Blueprint $table) {
            $table->dropForeign(['module_id']);
        });

        $this->safeTable('tests_finaux', function (Blueprint $table) {
            $table->dropForeign(['cours_id']);
        });
    }

    /**
     * Exécuter les modifications de table de manière sécurisée en ignorant les erreurs de clé/index dupliqués.
     */
    private function safeTable(string $table, \Closure $callback): void
    {
        try {
            Schema::table($table, $callback);
        } catch (\Exception $e) {
            // Ignorer l'erreur si la contrainte ou l'index existe déjà
        }
    }
};
