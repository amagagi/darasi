<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfigTentativesTableSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            [
                'test_id' => 1,
                'test_final_id' => null,
                'max_tentatives' => 3,
                'delai_heures' => 24,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'test_id' => null,
                'test_final_id' => 1,
                'max_tentatives' => 2,
                'delai_heures' => 48,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($configs as $config) {
            DB::table('config_tentatives')->updateOrInsert(
                [
                    'test_id' => $config['test_id'],
                    'test_final_id' => $config['test_final_id'],
                ],
                $config
            );
        }
    }
}