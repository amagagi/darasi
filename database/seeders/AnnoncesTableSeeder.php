<?php

namespace Database\Seeders;

use App\Models\Annonce;
use Illuminate\Database\Seeder;

/**
 * Jeu d'annonces de démonstration.
 *
 * VOLONTAIREMENT ABSENT de DatabaseSeeder : l'entrypoint Docker exécute
 * `migrate --force --seed` à chaque démarrage du conteneur, ce qui publierait
 * ces fausses actualités en production. À lancer explicitement :
 *
 *     php artisan db:seed --class=AnnoncesTableSeeder
 *
 * Pour repartir de zéro :
 *
 *     php artisan tinker --execute="\App\Models\Annonce::truncate();"
 */
class AnnoncesTableSeeder extends Seeder
{
    public function run(): void
    {
        $annonces = [
            // ── Bandeau, priorité la plus haute : c'est celle qui s'affiche
            //    en premier, les autres passent derrière le compteur « +N ».
            [
                'titre' => 'Ouverture des inscriptions à la session de septembre',
                'extrait' => 'Les inscriptions à tous les parcours certifiants sont ouvertes jusqu\'au 30 septembre. Places limitées.',
                'contenu' => '<p>Nos parcours certifiants rouvrent leurs inscriptions pour la session de septembre.</p><p>Chaque parcours comprend des modules vidéo, des quiz de validation et un certificat vérifiable à l\'issue du test final.</p>',
                'type' => 'succes',
                'cible' => 'tous',
                'afficher_banniere' => true,
                'afficher_actualites' => true,
                'lien_url' => 'https://darasihub.com/#cours',
                'lien_libelle' => 'Voir le catalogue',
                'priorite' => 30,
                'publiee_le' => now()->subDays(2),
            ],

            // ── Deuxième du bandeau : permet de voir le compteur « +1 » et le
            //    passage à l'annonce suivante quand on ferme la première.
            [
                'titre' => 'Nouveau parcours : Flutter avancé',
                'extrait' => 'Animations, performance et publication sur les stores : le nouveau module Flutter avancé est en ligne.',
                'contenu' => '<p>Un parcours de 6 modules consacré aux animations, à l\'optimisation des performances et à la mise en production d\'une application Flutter.</p>',
                'type' => 'info',
                'cible' => 'tous',
                'afficher_banniere' => true,
                'afficher_actualites' => true,
                'priorite' => 20,
                'publiee_le' => now()->subDays(5),
            ],

            // ── Réservée aux utilisateurs connectés : invisible pour un
            //    visiteur de la page d'accueil, visible dans le tableau de bord.
            [
                'titre' => 'Vos certificats sont désormais téléchargeables en PDF',
                'extrait' => 'Retrouvez vos certificats dans votre espace personnel, au format PDF vérifiable.',
                'contenu' => '<p>Chaque certificat comporte un code de vérification consultable publiquement.</p>',
                'type' => 'succes',
                'cible' => 'connectes',
                'afficher_banniere' => true,
                'afficher_actualites' => true,
                'priorite' => 15,
                'publiee_le' => now()->subDay(),
            ],

            // ── Section Actualités uniquement : démontre qu'une annonce peut
            //    exister sans mobiliser le bandeau du haut.
            [
                'titre' => 'Trois nouveaux formateurs rejoignent DARASI',
                'extrait' => 'Développement mobile, cybersécurité et analyse de données : nos équipes s\'agrandissent.',
                'contenu' => '<p>Trois praticiens rejoignent l\'équipe pédagogique pour renforcer nos pôles développement mobile, cybersécurité et analyse de données.</p>',
                'type' => 'info',
                'cible' => 'public',
                'afficher_banniere' => false,
                'afficher_actualites' => true,
                'priorite' => 10,
                'publiee_le' => now()->subWeek(),
            ],

            // ── Avec date d'expiration : disparaîtra d'elle-même dans 3 jours.
            [
                'titre' => 'Maintenance planifiée dans la nuit de samedi à dimanche',
                'extrait' => 'La plateforme sera indisponible de 23h00 à 02h00 pour une mise à jour technique.',
                'contenu' => '<p>Une interruption de service est prévue pour la mise à jour de nos serveurs. Vos progressions et certificats ne sont pas affectés.</p>',
                'type' => 'avertissement',
                'cible' => 'tous',
                'afficher_banniere' => false,
                'afficher_actualites' => true,
                'priorite' => 5,
                'publiee_le' => now()->subHours(6),
                'expire_le' => now()->addDays(3),
            ],
        ];

        foreach ($annonces as $donnees) {
            // firstOrCreate sur le titre : relancer le seeder ne duplique rien
            // et n'écrase pas les réglages modifiés depuis le back-office.
            Annonce::firstOrCreate(
                ['titre' => $donnees['titre']],
                $donnees + ['est_publiee' => true],
            );
        }
    }
}
