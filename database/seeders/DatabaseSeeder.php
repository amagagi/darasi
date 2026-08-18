<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Désactive les vérifications de clés étrangères pour vider les tables sans erreur
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Vider les tables (ordre non important grâce à la ligne ci-dessus)
        DB::table('abonnements_cours')->truncate();
        DB::table('abonnements_souscrits')->truncate();
        DB::table('abonnements_types')->truncate();
        DB::table('autorisations_correction')->truncate();
        DB::table('cache')->truncate();
        DB::table('categories')->truncate();
        DB::table('certificats')->truncate();
        DB::table('choix_questions')->truncate();
        DB::table('config_tentatives')->truncate();
        DB::table('corrections_ouvertes')->truncate();
        DB::table('cours')->truncate();
        DB::table('demandes_formation')->truncate();
        DB::table('inscriptions')->truncate();
        DB::table('lecons')->truncate();
        DB::table('messages')->truncate();
        DB::table('modules')->truncate();
        DB::table('niveaux')->truncate();
        DB::table('notifications')->truncate();
        DB::table('paiements')->truncate();
        DB::table('password_reset_tokens')->truncate(); // ✅ AJOUTE CETTE LIGNE ICI
        DB::table('jobs')->truncate(); // ✅ AJOUTE CETTE LIGNE ICI
        DB::table('paiements_logs')->truncate();
        DB::table('poles')->truncate();
        DB::table('progres_lecons')->truncate();
        DB::table('questions')->truncate();
        DB::table('reponses_questions')->truncate();
        DB::table('tentatives_tests')->truncate();
        DB::table('tentatives_test_final')->truncate();
        DB::table('tests')->truncate();
        DB::table('tests_finaux')->truncate();
        DB::table('users')->truncate();

        // Réactiver les vérifications de clés étrangères
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ============================================================
        // 1. PÔLES
        // ============================================================
        DB::table('poles')->insert([
            ['id' => 1, 'nom' => 'IT', 'description' => 'Cours d\'informatique et technologies', 'slug' => 'it', 'ordre' => 1, 'is_active' => 1, 'created_at' => '2026-07-02 09:02:49', 'updated_at' => '2026-07-02 09:02:49'],
            ['id' => 2, 'nom' => 'Scolaire', 'description' => 'Préparation examens CFEPD, BEPC, BAC', 'slug' => 'scolaire', 'ordre' => 2, 'is_active' => 1, 'created_at' => '2026-07-02 09:02:49', 'updated_at' => '2026-07-02 09:02:49'],
            ['id' => 3, 'nom' => 'Etudiant', 'description' => 'Renforcement universitaire', 'slug' => 'etudiant', 'ordre' => 3, 'is_active' => 1, 'created_at' => '2026-07-02 09:02:49', 'updated_at' => '2026-07-02 09:02:49'],
        ]);

        // ============================================================
        // 2. CATÉGORIES
        // ============================================================
        DB::table('categories')->insert([
            ['id' => 1, 'pole_id' => 1, 'nom' => 'Programmation', 'description' => 'Langages et frameworks', 'slug' => 'programmation', 'ordre' => 1, 'created_at' => '2026-07-02 09:02:51', 'updated_at' => '2026-07-02 09:02:51'],
            ['id' => 2, 'pole_id' => 1, 'nom' => 'Data Science', 'description' => 'Analyse de données et IA', 'slug' => 'data-science', 'ordre' => 2, 'created_at' => '2026-07-02 09:02:51', 'updated_at' => '2026-07-02 09:02:51'],
            ['id' => 3, 'pole_id' => 1, 'nom' => 'Design Web', 'description' => 'UI/UX et design', 'slug' => 'design-web', 'ordre' => 3, 'created_at' => '2026-07-02 09:02:51', 'updated_at' => '2026-07-02 09:02:51'],
            ['id' => 4, 'pole_id' => 2, 'nom' => 'Mathématiques', 'description' => 'Cours de maths', 'slug' => 'mathematiques', 'ordre' => 1, 'created_at' => '2026-07-02 09:02:51', 'updated_at' => '2026-07-02 09:02:51'],
            ['id' => 5, 'pole_id' => 2, 'nom' => 'Français', 'description' => 'Cours de français', 'slug' => 'francais', 'ordre' => 2, 'created_at' => '2026-07-02 09:02:51', 'updated_at' => '2026-07-02 09:02:51'],
            ['id' => 6, 'pole_id' => 3, 'nom' => 'Informatique', 'description' => 'Cours informatique universitaire', 'slug' => 'informatique', 'ordre' => 1, 'created_at' => '2026-07-02 09:02:51', 'updated_at' => '2026-07-02 09:02:51'],
        ]);

        // ============================================================
        // 3. NIVEAUX
        // ============================================================
        DB::table('niveaux')->insert([
            ['id' => 1, 'pole_id' => 2, 'libelle' => 'CFEPD', 'description' => null, 'ordre' => 1, 'created_at' => '2026-06-08 13:30:57', 'updated_at' => '2026-06-08 13:30:57'],
            ['id' => 2, 'pole_id' => 2, 'libelle' => 'BEPC', 'description' => null, 'ordre' => 2, 'created_at' => '2026-06-08 13:30:57', 'updated_at' => '2026-06-08 13:30:57'],
            ['id' => 3, 'pole_id' => 2, 'libelle' => 'BAC', 'description' => null, 'ordre' => 3, 'created_at' => '2026-06-08 13:30:57', 'updated_at' => '2026-06-08 13:30:57'],
            ['id' => 4, 'pole_id' => 3, 'libelle' => 'Licence 1', 'description' => null, 'ordre' => 1, 'created_at' => '2026-06-08 13:30:57', 'updated_at' => '2026-06-08 13:30:57'],
            ['id' => 5, 'pole_id' => 3, 'libelle' => 'Licence 2', 'description' => null, 'ordre' => 2, 'created_at' => '2026-06-08 13:30:57', 'updated_at' => '2026-06-08 13:30:57'],
            ['id' => 6, 'pole_id' => 3, 'libelle' => 'Licence 3', 'description' => null, 'ordre' => 3, 'created_at' => '2026-06-08 13:30:57', 'updated_at' => '2026-06-08 13:30:57'],
            ['id' => 7, 'pole_id' => 3, 'libelle' => 'Master 1', 'description' => null, 'ordre' => 4, 'created_at' => '2026-06-08 13:30:57', 'updated_at' => '2026-06-08 13:30:57'],
            ['id' => 8, 'pole_id' => 3, 'libelle' => 'Master 2', 'description' => null, 'ordre' => 5, 'created_at' => '2026-06-08 13:30:57', 'updated_at' => '2026-06-08 13:30:57'],
        ]);

        // ============================================================
        // 4. ABONNEMENTS TYPES
        // ============================================================
        DB::table('abonnements_types')->insert([
            ['id' => 1, 'categorie_id' => null, 'nom' => 'Abonnement Mensuel', 'description' => 'Accès illimité à tous les cours pendant 30 jours', 'duree_jours' => 30, 'prix' => 5000, 'nb_cours_max' => null, 'est_populaire' => 1, 'est_actif' => 1, 'ordre' => 1, 'created_at' => '2026-06-08 13:30:57', 'updated_at' => '2026-06-08 13:30:57'],
            ['id' => 2, 'categorie_id' => null, 'nom' => 'Abonnement Trimestriel', 'description' => 'Accès illimité à tous les cours pendant 90 jours', 'duree_jours' => 90, 'prix' => 12000, 'nb_cours_max' => null, 'est_populaire' => 0, 'est_actif' => 1, 'ordre' => 2, 'created_at' => '2026-06-08 13:30:57', 'updated_at' => '2026-06-08 13:30:57'],
            ['id' => 3, 'categorie_id' => null, 'nom' => 'Abonnement Annuel', 'description' => 'Accès illimité à tous les cours pendant 365 jours', 'duree_jours' => 365, 'prix' => 40000, 'nb_cours_max' => null, 'est_populaire' => 1, 'est_actif' => 1, 'ordre' => 3, 'created_at' => '2026-06-08 13:30:57', 'updated_at' => '2026-06-08 13:30:57'],
        ]);

        // ============================================================
        // 5. UTILISATEURS
        // ============================================================
        DB::table('users')->insert([
            ['id' => 1, 'nom' => 'Admin', 'prenom' => 'System', 'email' => 'admin@darasi.com', 'telephone' => '+22790000000', 'email_verified_at' => '2026-07-02 09:02:50', 'password' => Hash::make('password'), 'role' => 'admin', 'is_active' => 1, 'deactivated_at' => null, 'deactivated_reason' => null, 'avatar' => null, 'remember_token' => null, 'created_at' => '2026-07-02 09:02:50', 'updated_at' => '2026-07-02 09:02:50'],
            ['id' => 11, 'nom' => 'Salaou', 'prenom' => 'Alio', 'email' => 'salaou@darasi.com', 'telephone' => '98567423', 'email_verified_at' => null, 'password' => Hash::make('password'), 'role' => 'formateur', 'is_active' => 1, 'deactivated_at' => null, 'deactivated_reason' => null, 'avatar' => null, 'remember_token' => null, 'created_at' => '2026-08-10 14:51:50', 'updated_at' => '2026-08-10 14:52:44'],
            ['id' => 12, 'nom' => 'Magagi Dan Gana', 'prenom' => 'Abdourahamane', 'email' => 'magagidangana07@gmail.com', 'telephone' => '92000159', 'email_verified_at' => null, 'password' => Hash::make('password'), 'role' => 'apprenant', 'is_active' => 1, 'deactivated_at' => null, 'deactivated_reason' => null, 'avatar' => null, 'remember_token' => null, 'created_at' => '2026-08-18 08:05:48', 'updated_at' => '2026-08-18 09:19:58'],
        ]);

        // ============================================================
        // 6. COURS
        // ============================================================
        DB::table('cours')->insert([
            ['id' => 18, 'titre' => 'Maîtrisez Laravel 11 : De zéro à expert', 'description' => 'Apprenez à construire des applications web modernes avec le framework Laravel 11. Ce cours couvre l\'architecture MVC, Eloquent, les routes, les vues Blade et l\'authentification.', 'objectifs' => '- Comprendre le cycle de vie d\'une requête Laravel<br>- Savoir créer des routes et des contrôleurs<br>- Maîtriser Eloquent ORM et les migrations', 'prerequis' => '- Connaissances de base en PHP<br>- Notions de base en HTML/CSS', 'pole_id' => 1, 'formateur_id' => 11, 'categorie_id' => 6, 'niveau_id' => 5, 'image_couverture' => 'cours/images/01KZP86ED39J77GVPJBPBR763R.png', 'video_presentation' => null, 'est_certifiant' => 0, 'note_minimale_certificat' => 70.00, 'prix' => 0, 'est_gratuit' => 1, 'statut' => 'publie', 'note_moyenne' => 0.00, 'nb_apprenants' => 0, 'created_at' => '2026-08-10 15:30:50', 'updated_at' => '2026-08-10 15:30:50', 'published_at' => '2026-08-10 16:02:15'],
        ]);

        // ============================================================
        // 7. MODULES
        // ============================================================
        DB::table('modules')->insert([
            ['id' => 30, 'cours_id' => 18, 'titre' => 'Fondamentaux et Installation', 'description' => 'Dans ce premier module, nous allons configurer notre environnement de développement et comprendre l\'architecture de base de Laravel.', 'ordre' => 1, 'duree_estimee' => 30, 'created_at' => '2026-08-10 15:36:21', 'updated_at' => '2026-08-10 15:36:21'],
            ['id' => 31, 'cours_id' => 18, 'titre' => 'Le Routage et les Contrôleurs', 'description' => 'Nous allons apprendre à gérer les URLs de notre site et à structurer la logique métier dans des contrôleurs.', 'ordre' => 2, 'duree_estimee' => 45, 'created_at' => '2026-08-10 15:37:06', 'updated_at' => '2026-08-10 15:37:06'],
        ]);

        // ============================================================
        // 8. LEÇONS
        // ============================================================
        DB::table('lecons')->insert([
            ['id' => 46, 'module_id' => 30, 'titre' => '1.1 - Introduction et installation de Wamp', 'type_contenu' => 'video', 'contenu_text' => null, 'url_video' => 'https://www.youtube.com/watch?v=mAFkRqe_TiM', 'url_pdf' => null, 'duree_video' => 300, 'ordre' => 1, 'created_at' => '2026-08-10 15:39:21', 'updated_at' => '2026-08-10 15:39:21'],
            ['id' => 47, 'module_id' => 30, 'titre' => 'Comprendre l\'architecture MVC', 'type_contenu' => 'article', 'contenu_text' => '<p>Le modèle MVC (Modèle-Vue-Contrôleur) est un patron de conception logiciel. Dans Laravel :<br>- Le <strong>Modèle</strong> gère les données (base de données).<br>- La <strong>Vue</strong> gère l&#039;affichage HTML.<br>- Le <strong>Contrôleur</strong> gère la logique métier.</p>', 'url_video' => null, 'url_pdf' => null, 'duree_video' => null, 'ordre' => 2, 'created_at' => '2026-08-10 15:40:19', 'updated_at' => '2026-08-10 15:40:19'],
            ['id' => 48, 'module_id' => 30, 'titre' => '1.3 - Votre première page : Ressource PDF', 'type_contenu' => 'pdf', 'contenu_text' => null, 'url_video' => null, 'url_pdf' => 'lecons/pdfs/01KZP8SA21ATVXBTWH92RV5TEM.pdf', 'duree_video' => null, 'ordre' => 3, 'created_at' => '2026-08-10 15:41:08', 'updated_at' => '2026-08-10 15:41:08'],
            ['id' => 49, 'module_id' => 31, 'titre' => '2.1 - Créer des routes et des contrôleurs', 'type_contenu' => 'video', 'contenu_text' => null, 'url_video' => 'https://www.youtube.com/watch?v=QQRLEMT9nac', 'url_pdf' => null, 'duree_video' => 420, 'ordre' => 1, 'created_at' => '2026-08-10 15:43:02', 'updated_at' => '2026-08-10 15:43:02'],
        ]);

        // ============================================================
        // 9. AUTORISATIONS CORRECTION & NOTIFICATIONS
        // ============================================================
        DB::table('autorisations_correction')->insert([
            ['id' => 2, 'formateur_id' => 11, 'cours_id' => 18, 'autorise_par' => 1, 'date_autorisation' => '2026-08-10 15:31:06', 'est_active' => 1, 'created_at' => '2026-08-10 15:30:50', 'updated_at' => '2026-08-10 15:31:06'],
        ]);

        DB::table('notifications')->insert([
            ['id' => 3, 'user_id' => 1, 'titre' => 'Test notification', 'message' => 'Ceci est un test', 'type' => 'systeme', 'est_lu' => 0, 'data' => '[]', 'created_at' => '2026-06-11 09:50:23', 'updated_at' => '2026-06-11 09:50:23'],
            ['id' => 26, 'user_id' => 11, 'titre' => '✅ Compte validé', 'message' => 'Votre compte formateur a été activé par l\'administrateur.', 'type' => 'systeme', 'est_lu' => 0, 'data' => '[]', 'created_at' => '2026-08-10 14:52:24', 'updated_at' => '2026-08-10 14:52:24'],
        ]);

        // ============================================================
        // 10. PASSWORD RESET TOKENS
        // ============================================================
        DB::table('password_reset_tokens')->insert([
            ['email' => 'magagidangana07@gmail.com', 'token' => '$2y$12$XHLU8VmS1Gm2eKyOdfKhXes2HxUfqQpO4l0Sghe77i4Du.L5.Ct3i', 'created_at' => '2026-08-18 09:18:04'],
        ]);
        
        // ============================================================
        // 11. JOBS
        // ============================================================
        DB::table('jobs')->insert([
            ['id' => 1, 'queue' => 'default', 'payload' => '{"uuid":"89e413e4-6294-4859-b34c-f3c8e79888c0","displayName":"Filament\\\\Auth\\\\Notifications\\\\ResetPassword","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"backoff":null,"timeout":null,"retryUntil":null,"deleteWhenMissingModels":false,"data":{"commandName":"Illuminate\\\\Notifications\\\\SendQueuedNotifications","command":"O:48:\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\":3:{s:11:\\"notifiables\\";O:45:\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\":5:{s:5:\\"class\\";s:15:\\"App\\\\Models\\\\User\\";s:2:\\"id\\";a:1:{i:0;i:12;}s:9:\\"relations\\";a:0:{}s:10:\\"connection\\";s:5:\\"mysql\\";s:15:\\"collectionClass\\";N;}s:12:\\"notification\\";O:41:\\"Filament\\\\Auth\\\\Notifications\\\\ResetPassword\\":3:{s:3:\\"url\\";s:228:\\"http:\\/\\/127.0.0.1:8000\\/admin\\/password-reset\\/reset?email=magagidangana07%40gmail.com&token=cddec28715ec1425b8f87e85eae6ba085b95f759adf6c5eea02fee70b41e8d12&signature=629a4475b5fd6d815b815acbc4024f3e01af75346cffbe362efbec632dfd0bd7\\";s:5:\\"token\\";s:64:\\"cddec28715ec1425b8f87e85eae6ba085b95f759adf6c5eea02fee70b41e8d12\\";s:2:\\"id\\";s:36:\\"d40846c7-4381-41db-aaf6-3324ceef8b06\\";}s:8:\\"channels\\";a:1:{i:0;s:4:\\"mail\\";}}","batchId":null},"createdAt":1787046861,"delay":null}', 'attempts' => 0, 'reserved_at' => null, 'available_at' => 1787046861, 'created_at' => 1787046861],
            ['id' => 2, 'queue' => 'default', 'payload' => '{"uuid":"ce06bfcc-b6e9-4f8d-baf7-8d2dafbcb652","displayName":"Filament\\\\Auth\\\\Notifications\\\\ResetPassword","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"backoff":null,"timeout":null,"retryUntil":null,"deleteWhenMissingModels":false,"data":{"commandName":"Illuminate\\\\Notifications\\\\SendQueuedNotifications","command":"O:48:\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\":3:{s:11:\\"notifiables\\";O:45:\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\":5:{s:5:\\"class\\";s:15:\\"App\\\\Models\\\\User\\";s:2:\\"id\\";a:1:{i:0;i:12;}s:9:\\"relations\\";a:0:{}s:10:\\"connection\\";s:5:\\"mysql\\";s:15:\\"collectionClass\\";N;}s:12:\\"notification\\";O:41:\\"Filament\\\\Auth\\\\Notifications\\\\ResetPassword\\":3:{s:3:\\"url\\";s:228:\\"http:\\/\\/localhost:8000\\/admin\\/password-reset\\/reset?email=magagidangana07%40gmail.com&token=9961dc910f1ea6230f847be11435695e17756a9b021cb168b4f3b1e7691a657d&signature=cdb87967c2078c4dcab7c81f37d96c8e4b2758fd55cb0fd5f87f76993475e53c\\";s:5:\\"token\\";s:64:\\"9961dc910f1ea6230f847be11435695e17756a9b021cb168b4f3b1e7691a657d\\";s:2:\\"id\\";s:36:\\"cf72847c-eb56-46a5-aedc-0d0316d9b3c1\\";}s:8:\\"channels\\";a:1:{i:0;s:4:\\"mail\\";}}","batchId":null},"createdAt":1787048087,"delay":null}', 'attempts' => 0, 'reserved_at' => null, 'available_at' => 1787048087, 'created_at' => 1787048087],
            ['id' => 3, 'queue' => 'default', 'payload' => '{"uuid":"d43c26ed-d7f8-42c6-b789-5b3440be8823","displayName":"Filament\\\\Auth\\\\Notifications\\\\ResetPassword","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"backoff":null,"timeout":null,"retryUntil":null,"deleteWhenMissingModels":false,"data":{"commandName":"Illuminate\\\\Notifications\\\\SendQueuedNotifications","command":"O:48:\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\":3:{s:11:\\"notifiables\\";O:45:\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\":5:{s:5:\\"class\\";s:15:\\"App\\\\Models\\\\User\\";s:2:\\"id\\";a:1:{i:0;i:12;}s:9:\\"relations\\";a:0:{}s:10:\\"connection\\";s:5:\\"mysql\\";s:15:\\"collectionClass\\";N;}s:12:\\"notification\\";O:41:\\"Filament\\\\Auth\\\\Notifications\\\\ResetPassword\\":3:{s:3:\\"url\\";s:228:\\"http:\\/\\/localhost:8000\\/admin\\/password-reset\\/reset?email=magagidangana07%40gmail.com&token=445baac71e2526de2bbd4843e0368fef1392af573a055f696d879a34348dc4af&signature=30db0a5db31ea698bd3971d148f47aa3665694c8f56d3243e33dda215d6d8273\\";s:5:\\"token\\";s:64:\\"445baac71e2526de2bbd4843e0368fef1392af573a055f696d879a34348dc4af\\";s:2:\\"id\\";s:36:\\"ef8b544b-47f1-4821-8f66-ee9526c2f151\\";}s:8:\\"channels\\";a:1:{i:0;s:4:\\"mail\\";}}","batchId":null},"createdAt":1787048173,"delay":null}', 'attempts' => 0, 'reserved_at' => null, 'available_at' => 1787048173, 'created_at' => 1787048173],
        ]);
        
        // ============================================================
        // 12. SESSIONS
        // ============================================================
        DB::table('sessions')->insert([
            ['id' => 'hnX9yyjaq5rKrU0zIAuc4nnFNgH3AbK8WKCZZGsU', 'user_id' => 1, 'ip_address' => '127.0.0.1', 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'payload' => 'eyJfdG9rZW4iOiJ2WlJVVzlqVzZadHNxRzhRcUtXWU5XQ1A1WHc0c2Iza3BrNmpZM21sIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvbG9jYWxob3N0OjgwMDBcL2FkbWluXC9jb3VycyIsInJvdXRlIjoiZmlsYW1lbnQuYWRtaW4ucmVzb3VyY2VzLmNvdXJzLmluZGV4In0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxLCJwYXNzd29yZF9oYXNoX3dlYiI6ImE1ZjI3NWVlOGJiNjhiODE3NDEwNzJlM2Q2MWQ0NjFiZjAxMTQ5YzgzMjdlNGNiN2I4MjJlZTcxMzZhMTVlMzMiLCJ0YWJsZXMiOnsiNWRmNTU2NzBlOWE2ZDM3Y2UwZTM5NDM1ODNjY2JiNTJfY29sdW1ucyI6W3sidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJhcHByZW5hbnQucHJlbm9tIiwibGFiZWwiOiJBcHByZW5hbnQiLCJpc0hpZGRlbiI6ZmFsc2UsImlzVG9nZ2xlZCI6dHJ1ZSwiaXNUb2dnbGVhYmxlIjpmYWxzZSwiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjpudWxsfSx7InR5cGUiOiJjb2x1bW4iLCJuYW1lIjoiY291cnMudGl0cmUiLCJsYWJlbCI6IkNvdXJzIiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOnRydWUsImlzVG9nZ2xlYWJsZSI6ZmFsc2UsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6bnVsbH0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6InByb2dyZXNzaW9uIiwibGFiZWwiOiJQcm9ncmVzc2lvbiIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJjcmVhdGVkX2F0IiwibGFiZWwiOiJEYXRlIiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOnRydWUsImlzVG9nZ2xlYWJsZSI6ZmFsc2UsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6bnVsbH1dLCI0NDFhNWZiNjZhODA4NzFhYjlmODA5ZjkxMjI1OTgxMF9jb2x1bW5zIjpbeyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6InRpdHJlIiwibGFiZWwiOiJUaXRyZSIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJmb3JtYXRldXJfaWQiLCJsYWJlbCI6IkZvcm1hdGV1ciIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJwb2xlLm5vbSIsImxhYmVsIjoiUFx1MDBmNGxlIiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOnRydWUsImlzVG9nZ2xlYWJsZSI6ZmFsc2UsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6bnVsbH0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6ImNhdGVnb3JpZS5ub20iLCJsYWJlbCI6IkNhdFx1MDBlOWdvcmllIiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOnRydWUsImlzVG9nZ2xlYWJsZSI6ZmFsc2UsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6bnVsbH0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6Im5pdmVhdS5saWJlbGxlIiwibGFiZWwiOiJOaXZlYXUiLCJpc0hpZGRlbiI6ZmFsc2UsImlzVG9nZ2xlZCI6dHJ1ZSwiaXNUb2dnbGVhYmxlIjpmYWxzZSwiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjpudWxsfSx7InR5cGUiOiJjb2x1bW4iLCJuYW1lIjoiaW1hZ2VfY291dmVydHVyZSIsImxhYmVsIjoiSW1hZ2UgY291dmVydHVyZSIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJlc3RfY2VydGlmaWFudCIsImxhYmVsIjoiRXN0IGNlcnRpZmlhbnQiLCJpc0hpZGRlbiI6ZmFsc2UsImlzVG9nZ2xlZCI6dHJ1ZSwiaXNUb2dnbGVhYmxlIjpmYWxzZSwiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjpudWxsfSx7InR5cGUiOiJjb2x1bW4iLCJuYW1lIjoicHJpeCIsImxhYmVsIjoiUHJpeCIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJzdGF0dXQiLCJsYWJlbCI6IlN0YXR1dCIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJhdXRvcmlzYXRpb25zX2NvcnJlY3Rpb24iLCJsYWJlbCI6IkNvcnJlY3RldXJzIGF1dG9yaXNcdTAwZTlzIiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOnRydWUsImlzVG9nZ2xlYWJsZSI6dHJ1ZSwiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjpmYWxzZX0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6Im5iX2FwcHJlbmFudHMiLCJsYWJlbCI6Ik5iIGFwcHJlbmFudHMiLCJpc0hpZGRlbiI6ZmFsc2UsImlzVG9nZ2xlZCI6dHJ1ZSwiaXNUb2dnbGVhYmxlIjpmYWxzZSwiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjpudWxsfSx7InR5cGUiOiJjb2x1bW4iLCJuYW1lIjoiY3JlYXRlZF9hdCIsImxhYmVsIjoiQ3JlYXRlZCBhdCIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9XX19', 'last_activity' => 1787048590],
        ]);
        
        $this->call([
            // Si tu as d'autres seeders spécifiques à des fonctionnalités, tu peux les appeler ici
        ]);
    }
}