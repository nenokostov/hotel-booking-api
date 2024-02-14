<?php

namespace Database\Factories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'room_id' => function () {
                return \App\Models\Room::factory()->create()->id;
            },
            'customer_id' => function () {
                return \App\Models\Customer::factory()->create()->id;
            },
            'check_in_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'check_out_date' => $this->faker->dateTimeBetween('+31 days', '+60 days'),
            'total_price' => $this->faker->randomFloat(2, 100, 1000),
        ];
    }
}
