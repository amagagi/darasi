<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TentativesTestFinalTableSeeder extends Seeder
{
    public function run(): void
    {
        $tentatives = [
            [
                'inscription_id' => 1,
                'test_final_id' => 1,
                'note' => 65.00,
                'est_reussi' => false,
                'tentative_numero' => 1,
                'date_tentative' => now()->subDays(5),
                'date_prochaine_autorisee' => now()->subDays(4),
                'a_obtenu_certificat' => false,
                'date_obtention_certificat' => null,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
            [
                'inscription_id' => 1,
                'test_final_id' => 1,
                'note' => 78.50,
                'est_reussi' => true,
                'tentative_numero' => 2,
                'date_tentative' => now()->subDays(3),
                'date_prochaine_autorisee' => null,
                'a_obtenu_certificat' => true,
                'date_obtention_certificat' => now()->subDays(3),
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
            [
                'inscription_id' => 2,
                'test_final_id' => 1,
                'note' => 82.00,
                'est_reussi' => true,
                'tentative_numero' => 1,
                'date_tentative' => now()->subDays(2),
                'date_prochaine_autorisee' => null,
                'a_obtenu_certificat' => true,
                'date_obtention_certificat' => now()->subDays(2),
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
        ];

        foreach ($tentatives as $tentative) {
            DB::table('tentatives_test_final')->updateOrInsert(
                [
                    'inscription_id' => $tentative['inscription_id'],
                    'test_final_id' => $tentative['test_final_id'],
                    'tentative_numero' => $tentative['tentative_numero'],
                ],
                $tentative
            );
        }
    }
}