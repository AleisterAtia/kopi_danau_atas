<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'midtrans_order_id' => 'KDA-'.fake()->unique()->numberBetween(1, 99999).'-'.fake()->numerify('##########'),
            'midtrans_transaction_id' => null,
            'snap_token' => null,
            'payment_type' => null,
            'status' => 'pending',
            'gross_amount' => 100000,
            'midtrans_response' => null,
            'paid_at' => null,
        ];
    }

    public function settled(): static
    {
        return $this->state(fn () => [
            'status' => 'settlement',
            'paid_at' => now(),
            'midtrans_transaction_id' => fake()->uuid(),
        ]);
    }
}
