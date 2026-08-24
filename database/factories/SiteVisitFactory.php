<?php

namespace Database\Factories;

use App\Models\SiteVisit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SiteVisit>
 */
class SiteVisitFactory extends Factory
{
    protected $model = SiteVisit::class;

    public function definition(): array
    {
        return [
            'visited_at' => now(),
            'ip_hash' => hash('sha256', fake()->ipv4()),
            'user_agent' => fake()->userAgent(),
            'page_url' => fake()->randomElement(['/welcome', '/plateformes', '/plateformes/darasi-lms']),
            'session_id' => Str::random(32),
        ];
    }
}
