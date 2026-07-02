<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CertificatsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('certificats')->updateOrInsert(
            ['id' => 1],
            [
                'inscription_id' => 1,
                'tentative_final_id' => 1,
                'code_verification' => 'CERT-' . strtoupper(uniqid()),
                'date_emission' => now(),
                'est_valide' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}