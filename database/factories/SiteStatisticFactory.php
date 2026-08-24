<?php

namespace Database\Factories;

use App\Models\SiteStatistic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SiteStatistic>
 */
class SiteStatisticFactory extends Factory
{
    protected $model = SiteStatistic::class;

    public function definition(): array
    {
        return [
            'label' => fake()->unique()->words(3, true),
            'value' => fake()->numberBetween(1, 2000).'+',
            'icon' => 'heroicon-o-star',
            'display_order' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
