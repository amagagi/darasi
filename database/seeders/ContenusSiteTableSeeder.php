<?php

namespace Database\Seeders;

use App\Models\ContenuSite;
use Illuminate\Database\Seeder;

class ContenusSiteTableSeeder extends Seeder
{
    /**
     * Valeurs de départ des sections « Vision & Mission » et « À propos ».
     *
     * `firstOrCreate` et non `updateOrCreate` : ce seeder est déclenché
     * manuellement (voir AnnoncesTableSeeder pour le pourquoi), et les textes
     * modifiés depuis le back-office ne doivent jamais être réécrasés en cas
     * de relance.
     */
    public function run(): void
    {
        $blocs = [
            [
                'cle' => ContenuSite::CLE_VISION,
                'titre' => 'Notre Vision',
                'sous_titre' => 'Rendre l\'excellence accessible à tous',
                'contenu' => "Faire du numérique un levier d'égalité des chances au Niger et dans toute la sous-région. Nous imaginons un espace où l'origine géographique, le débit de connexion ou le budget ne déterminent plus l'accès au savoir de qualité.\n\nÀ terme, chaque apprenant motivé doit pouvoir se former aux compétences les plus recherchées du marché, depuis là où il se trouve, et faire reconnaître ces compétences.",
                'icone' => 'visibility',
                'ordre' => 1,
            ],
            [
                'cle' => ContenuSite::CLE_MISSION,
                'titre' => 'Notre Mission',
                'sous_titre' => 'Former, accompagner, certifier',
                'contenu' => "Concevoir des parcours de formation courts, concrets et directement applicables, portés par des formateurs praticiens de leur domaine.\n\nAccompagner chaque apprenant de son inscription à sa certification : supports légers adaptés aux connexions bas débit, quiz de validation, suivi de progression et certificats vérifiables reconnus par nos entreprises partenaires.",
                'icone' => 'flag',
                'ordre' => 2,
            ],
            [
                'cle' => ContenuSite::CLE_VALEURS,
                'titre' => 'Nos Valeurs',
                'sous_titre' => 'Ce qui guide nos décisions',
                'contenu' => "Exigence pédagogique : nous préférons un contenu de moins, bien construit, à dix contenus de remplissage.\n\nProximité : nous restons joignables et nos formateurs répondent réellement.\n\nIntégrité : nous ne promettons pas un emploi, nous délivrons des compétences vérifiables.",
                'icone' => 'favorite',
                'ordre' => 3,
            ],
            [
                'cle' => ContenuSite::CLE_A_PROPOS,
                'titre' => 'Qui sommes-nous ?',
                'sous_titre' => null,
                'contenu' => "Nous sommes plus que des simples consultants, des formateurs et des coachs. Nous sommes vos partenaires dans le succès !\n\nNous sommes engagés à aider les personnes et les organisations à naviguer dans la complexité de leurs choix stratégiques et opérationnels.",
                'icone' => null,
                'ordre' => 4,
            ],
        ];

        foreach ($blocs as $bloc) {
            ContenuSite::firstOrCreate(
                ['cle' => $bloc['cle']],
                $bloc + ['est_actif' => true],
            );
        }
    }
}
