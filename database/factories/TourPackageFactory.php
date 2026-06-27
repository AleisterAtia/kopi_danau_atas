<?php

namespace Database\Factories;

use App\Models\TourPackage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TourPackage>
 */
class TourPackageFactory extends Factory
{
    protected $model = TourPackage::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->paragraph(),
            'price' => fake()->numberBetween(50_000, 500_000),
            'duration_hours' => fake()->numberBetween(2, 8),
            'daily_capacity' => 20,
            'facilities' => null,
            'is_active' => true,
            'is_featured' => false,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }
}
