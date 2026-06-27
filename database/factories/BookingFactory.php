<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'booking_code' => 'KDA-'.now()->format('Ymd').'-'.fake()->unique()->numberBetween(10000, 99999),
            'user_id' => User::factory(),
            'tour_package_id' => TourPackage::factory(),
            'visit_date' => now()->addDays(7)->toDateString(),
            'guest_count' => 1,
            'guest_name' => fake()->name(),
            'guest_phone' => fake()->numerify('08#########'),
            'guest_email' => fake()->safeEmail(),
            'notes' => null,
            'total_price' => 100000,
            'status' => 'pending',
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => ['status' => 'paid']);
    }

    public function completed(): static
    {
        return $this->state(fn () => ['status' => 'completed']);
    }
}
