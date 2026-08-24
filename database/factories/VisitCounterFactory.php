<?php

namespace Database\Factories;

use App\Models\VisitCounter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VisitCounter>
 */
class VisitCounterFactory extends Factory
{
    protected $model = VisitCounter::class;

    public function definition(): array
    {
        return [
            'date' => today(),
            'today_visits' => fake()->numberBetween(1, 100),
            'total_visits' => fake()->numberBetween(100, 5000),
        ];
    }
}
