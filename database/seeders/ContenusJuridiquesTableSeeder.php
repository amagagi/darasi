<?php

namespace Database\Seeders;

use App\Models\ContenuJuridique;
use Illuminate\Database\Seeder;

class ContenusJuridiquesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contenus = [
            [
                'type' => 'cgu',
                'titre' => "Conditions d'utilisation",
                'contenu' => '<h1>Conditions d\'utilisation</h1>
                    <h2>1. Objet</h2>
                    <p>Les présentes Conditions d\'utilisation régissent l\'accès et l\'usage de la plateforme de formation (DARASI). En créant un compte, l\'utilisateur accepte pleinement ces conditions.</p>
                    
                    <h2>2. Création de compte</h2>
                    <ul>
                        <li>L\'utilisateur doit fournir des informations exactes et à jour.</li>
                        <li>Le compte est strictement personnel et ne peut être partagé.</li>
                        <li>Toute activité réalisée via le compte est réputée effectuée par l\'utilisateur.</li>
                    </ul>
                    
                    <h2>3. Accès aux contenus</h2>
                    <ul>
                        <li>Les contenus pédagogiques (cours, vidéos, supports, exercices) sont protégés par le droit d\'auteur.</li>
                        <li>L\'utilisateur bénéficie d\'un droit d\'usage individuel, non transférable et non commercial.</li>
                        <li>Toute reproduction, diffusion ou extraction non autorisée est interdite.</li>
                    </ul>
                    
                    <h2>4. Obligations de l\'utilisateur</h2>
                    <ul>
                        <li>Respecter les règles de bonne conduite et les Règles de la plateforme.</li>
                        <li>Ne pas tenter de contourner les systèmes de sécurité.</li>
                        <li>Ne pas utiliser la Plateforme à des fins frauduleuses, illégales ou nuisibles.</li>
                    </ul>
                    
                    <h2>5. Données personnelles</h2>
                    <ul>
                        <li>Les données sont traitées conformément à la Politique de confidentialité.</li>
                        <li>Elles sont utilisées pour la gestion du compte, le suivi pédagogique et l\'amélioration des services.</li>
                        <li>L\'utilisateur peut exercer ses droits d\'accès, de rectification et de suppression.</li>
                    </ul>
                    
                    <h2>6. Responsabilités</h2>
                    <ul>
                        <li>La Plateforme s\'efforce d\'assurer un accès continu mais ne garantit pas l\'absence d\'interruptions.</li>
                        <li>Elle ne peut être tenue responsable en cas de mauvaise utilisation ou de non-respect des présentes conditions.</li>
                    </ul>
                    
                    <h2>7. Suspension ou suppression du compte</h2>
                    <p>La Plateforme peut suspendre ou supprimer un compte en cas de :</p>
                    <ul>
                        <li>non-respect des Conditions d\'utilisation,</li>
                        <li>fraude,</li>
                        <li>comportement inapproprié,</li>
                        <li>violation des droits d\'auteur.</li>
                    </ul>
                    
                    <h2>8. Modification des conditions</h2>
                    <p>La Plateforme peut modifier les présentes conditions. L\'utilisateur sera informé et devra accepter les nouvelles versions pour continuer à utiliser les services.</p>
                    
                    <h2>9. Acceptation</h2>
                    <p>En s\'inscrivant, l\'utilisateur confirme avoir lu et accepté les présentes Conditions d\'utilisation, la Politique de confidentialité et les Règles de la plateforme.</p>',
                'est_actif' => true,
                'modifie_par' => 1,
            ],
            [
                'type' => 'politique_confidentialite',
                'titre' => 'Politique de confidentialité',
                'contenu' => '<h1>Politique de confidentialité</h1>
                    <h2>1. Introduction</h2>
                    <p>La présente Politique de confidentialité explique comment la Plateforme collecte, utilise, stocke et protège les données personnelles de ses utilisateurs. En utilisant la Plateforme, l\'utilisateur accepte les pratiques décrites dans cette politique.</p>
                    
                    <h2>2. Données collectées</h2>
                    <p>La Plateforme peut collecter les catégories de données suivantes :</p>
                    <ul>
                        <li><strong>Données d\'identification</strong> : nom, prénom, adresse e‑mail, numéro de téléphone.</li>
                        <li><strong>Données de connexion</strong> : identifiants, logs, adresse IP, type d\'appareil.</li>
                        <li><strong>Données pédagogiques</strong> : parcours de formation, résultats, activités, certificats.</li>
                        <li><strong>Données techniques</strong> : cookies, statistiques d\'usage, préférences d\'affichage.</li>
                    </ul>
                    
                    <h2>3. Finalités du traitement</h2>
                    <p>Les données sont collectées pour :</p>
                    <ul>
                        <li>la création et gestion du compte utilisateur ;</li>
                        <li>le suivi pédagogique et la délivrance des attestations ;</li>
                        <li>l\'amélioration de l\'expérience utilisateur ;</li>
                        <li>la sécurisation de la Plateforme ;</li>
                        <li>le respect des obligations légales.</li>
                    </ul>
                    
                    <h2>4. Base légale</h2>
                    <p>Les traitements reposent sur :</p>
                    <ul>
                        <li>l\'exécution du contrat (accès aux formations) ;</li>
                        <li>le consentement de l\'utilisateur (cookies, communications) ;</li>
                        <li>l\'intérêt légitime (amélioration du service) ;</li>
                        <li>le respect d\'obligations légales.</li>
                    </ul>
                    
                    <h2>5. Partage des données</h2>
                    <p>Les données peuvent être partagées avec :</p>
                    <ul>
                        <li>les formateurs et administrateurs de la Plateforme ;</li>
                        <li>des prestataires techniques (hébergement, analytics, support) ;</li>
                        <li>des organismes certificateurs, si applicable.</li>
                    </ul>
                    <p>Aucune donnée n\'est vendue à des tiers.</p>
                    
                    <h2>6. Durée de conservation</h2>
                    <p>Les données sont conservées :</p>
                    <ul>
                        <li>tant que le compte est actif ;</li>
                        <li>puis archivées pendant la durée légale nécessaire ;</li>
                        <li>ou supprimées sur demande, sauf obligation contraire.</li>
                    </ul>
                    
                    <h2>7. Sécurité des données</h2>
                    <p>La Plateforme met en œuvre des mesures techniques et organisationnelles pour protéger les données :</p>
                    <ul>
                        <li>chiffrement,</li>
                        <li>contrôle d\'accès,</li>
                        <li>sauvegardes régulières,</li>
                        <li>surveillance des accès.</li>
                    </ul>
                    
                    <h2>8. Droits de l\'utilisateur</h2>
                    <p>Conformément au RGPD, l\'utilisateur dispose des droits suivants :</p>
                    <ul>
                        <li>droit d\'accès ;</li>
                        <li>droit de rectification ;</li>
                        <li>droit à l\'effacement ;</li>
                        <li>droit à la limitation ;</li>
                        <li>droit d\'opposition ;</li>
                        <li>droit à la portabilité.</li>
                    </ul>
                    <p>Toute demande peut être adressée via la rubrique <strong>Contact RGPD</strong>.</p>
                    
                    <h2>9. Cookies</h2>
                    <p>La Plateforme utilise des cookies pour :</p>
                    <ul>
                        <li>assurer le fonctionnement du site ;</li>
                        <li>analyser l\'usage ;</li>
                        <li>personnaliser l\'expérience.</li>
                    </ul>
                    <p>L\'utilisateur peut gérer ses préférences dans la section <strong>Gestion des cookies</strong>.</p>
                    
                    <h2>10. Modifications de la politique</h2>
                    <p>La Plateforme peut mettre à jour la présente Politique de confidentialité. L\'utilisateur sera informé en cas de modification importante.</p>
                    
                    <h2>11. Acceptation</h2>
                    <p>En utilisant la Plateforme, l\'utilisateur confirme avoir lu et accepté la présente Politique de confidentialité ainsi que les Conditions d\'utilisation.</p>',
                'est_actif' => true,
                'modifie_par' => 1,
            ],
            [
                'type' => 'regles_plateforme',
                'titre' => 'Règles de la plateforme',
                'contenu' => '<h1>Règles de la plateforme</h1>
                    <h2>1. Respect et comportement</h2>
                    <p>Chaque utilisateur s\'engage à adopter un comportement respectueux envers :</p>
                    <ul>
                        <li>les autres apprenants,</li>
                        <li>les formateurs,</li>
                        <li>l\'équipe d\'administration.</li>
                    </ul>
                    <p>Tout propos offensant, discriminatoire, violent ou inapproprié est strictement interdit.</p>
                    
                    <h2>2. Utilisation du compte</h2>
                    <ul>
                        <li>Le compte est strictement personnel.</li>
                        <li>Le partage d\'identifiants, la connexion simultanée depuis plusieurs personnes ou la revente d\'accès sont interdits.</li>
                        <li>L\'utilisateur doit protéger la confidentialité de ses identifiants.</li>
                    </ul>
                    
                    <h2>3. Utilisation des contenus</h2>
                    <p>Les contenus pédagogiques (cours, vidéos, supports, quiz, évaluations) sont protégés par le droit d\'auteur. Il est interdit de :</p>
                    <ul>
                        <li>les copier,</li>
                        <li>les partager,</li>
                        <li>les publier,</li>
                        <li>les diffuser,</li>
                        <li>les utiliser à des fins commerciales.</li>
                    </ul>
                    <p>L\'usage est limité au cadre de la formation suivie.</p>
                    
                    <h2>4. Participation aux activités</h2>
                    <p>L\'utilisateur s\'engage à :</p>
                    <ul>
                        <li>réaliser les activités et évaluations de manière personnelle et honnête ;</li>
                        <li>ne pas tricher, contourner les systèmes d\'évaluation ou utiliser des moyens frauduleux ;</li>
                        <li>respecter les délais et consignes pédagogiques.</li>
                    </ul>
                    
                    <h2>5. Sécurité et intégrité de la plateforme</h2>
                    <p>Il est interdit de :</p>
                    <ul>
                        <li>tenter d\'accéder à des espaces non autorisés ;</li>
                        <li>modifier, contourner ou désactiver les systèmes de sécurité ;</li>
                        <li>introduire des virus, scripts malveillants ou tout élément perturbateur ;</li>
                        <li>perturber le fonctionnement normal de la plateforme.</li>
                    </ul>
                    
                    <h2>6. Communication interne</h2>
                    <p>Les espaces de discussion, forums ou messageries doivent être utilisés pour :</p>
                    <ul>
                        <li>échanger sur les contenus de formation,</li>
                        <li>poser des questions pertinentes,</li>
                        <li>collaborer dans un cadre pédagogique.</li>
                    </ul>
                    <p>Toute utilisation abusive ou promotionnelle non autorisée est interdite.</p>
                    
                    <h2>7. Sanctions</h2>
                    <p>En cas de non‑respect des règles, la plateforme peut appliquer :</p>
                    <ul>
                        <li>un avertissement,</li>
                        <li>une suspension temporaire,</li>
                        <li>une suppression définitive du compte,</li>
                        <li>l\'annulation d\'une certification ou d\'un accès à une formation.</li>
                    </ul>
                    
                    <h2>8. Signalement</h2>
                    <p>Tout utilisateur peut signaler un comportement inapproprié via la rubrique Support et signalement. Les signalements sont traités de manière confidentielle.</p>
                    
                    <h2>9. Acceptation</h2>
                    <p>En utilisant la plateforme, l\'utilisateur confirme accepter les présentes Règles de la plateforme, ainsi que les Conditions d\'utilisation et la Politique de confidentialité.</p>',
                'est_actif' => true,
                'modifie_par' => 1,
            ],
        ];

        foreach ($contenus as $contenu) {
            ContenuJuridique::updateOrCreate(
                ['type' => $contenu['type']],
                $contenu
            );
        }
    }
}