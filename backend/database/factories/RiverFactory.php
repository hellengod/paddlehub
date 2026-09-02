<?php

namespace Database\Factories;

use App\Models\River;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<River>
 */
class RiverFactory extends Factory
{
    protected $model = River::class;

    public function definition(): array
    {
        $startLatitude = $this->faker->latitude(-30, 5);
        $startLongitude = $this->faker->longitude(-75, -35);
        $endLatitude = max(-89.9999999, min(89.9999999, $startLatitude + $this->faker->randomFloat(4, -0.45, 0.45)));
        $endLongitude = max(-179.9999999, min(179.9999999, $startLongitude + $this->faker->randomFloat(4, -0.45, 0.45)));

        return [
            'name' => 'Rio '.$this->faker->unique()->word(),
            'city' => $this->faker->city(),
            'state' => $this->faker->randomElement(['SP', 'RJ', 'MG', 'PR', 'SC', 'RS']),
            'difficulty_class' => $this->faker->randomElement(River::DIFFICULTY_CLASSES),
            'description' => $this->faker->sentence(12),
            'start_latitude' => $startLatitude,
            'start_longitude' => $startLongitude,
            'end_latitude' => $endLatitude,
            'end_longitude' => $endLongitude,
            'created_by' => User::factory(),
        ];
    }
}
