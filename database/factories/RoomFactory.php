<?php

namespace Database\Factories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'number' => $this->faker->unique()->randomNumber(),
            'type' => $this->faker->randomElement(['single', 'double', 'suite']),
            'price_per_night' => $this->faker->randomFloat(2, 50, 500),
            'status' => $this->faker->randomElement(['available', 'booked', 'maintenance']),
        ];
    }
}
