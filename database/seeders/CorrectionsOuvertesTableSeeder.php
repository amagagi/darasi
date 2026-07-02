<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CorrectionsOuvertesTableSeeder extends Seeder
{
    public function run(): void
    {
        $corrections = [
            [
                'reponse_id' => 1,
                'note_accordee' => 3.5,
                'commentaire' => 'Bonne réponse, mais manque de détails.',
                'corrige_par' => 2, // formateur id=2
                'date_correction' => now()->subDays(2),
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'reponse_id' => 2,
                'note_accordee' => 4.0,
                'commentaire' => 'Très bonne réponse, complet et bien structuré.',
                'corrige_par' => 2,
                'date_correction' => now()->subDays(1),
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
        ];

        foreach ($corrections as $correction) {
            DB::table('corrections_ouvertes')->updateOrInsert(
                [
                    'reponse_id' => $correction['reponse_id'],
                ],
                $correction
            );
        }
    }
}