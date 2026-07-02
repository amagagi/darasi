<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TentativesTestsTableSeeder extends Seeder
{
    public function run(): void
    {
        $tentatives = [
            [
                'inscription_id' => 1,
                'test_id' => 1,
                'note' => 8.5,
                'est_valide' => false,
                'tentative_numero' => 1,
                'date_tentative' => now()->subDays(2),
                'date_prochaine_autorisee' => now()->subDays(1),
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'inscription_id' => 1,
                'test_id' => 1,
                'note' => 12.0,
                'est_valide' => true,
                'tentative_numero' => 2,
                'date_tentative' => now()->subDays(1),
                'date_prochaine_autorisee' => null,
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
            [
                'inscription_id' => 2,
                'test_id' => 1,
                'note' => 15.5,
                'est_valide' => true,
                'tentative_numero' => 1,
                'date_tentative' => now()->subDays(3),
                'date_prochaine_autorisee' => null,
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
        ];

        foreach ($tentatives as $tentative) {
            DB::table('tentatives_tests')->updateOrInsert(
                [
                    'inscription_id' => $tentative['inscription_id'],
                    'test_id' => $tentative['test_id'],
                    'tentative_numero' => $tentative['tentative_numero'],
                ],
                $tentative
            );
        }
    }
}