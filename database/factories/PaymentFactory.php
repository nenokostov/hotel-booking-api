<?php

namespace Database\Factories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'booking_id' => function () {
                return \App\Models\Booking::factory()->create()->id;
            },
            'amount' => $this->faker->randomFloat(2, 10, 200),
            'payment_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'status' => $this->faker->randomElement(['completed', 'pending', 'failed']),
        ];
    }
}
