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
        return [
            'name' => 'Rio '.$this->faker->unique()->word(),
            'city' => $this->faker->city(),
            'state' => $this->faker->randomElement(['SP', 'RJ', 'MG', 'PR', 'SC', 'RS']),
            'difficulty_class' => $this->faker->randomElement(River::DIFFICULTY_CLASSES),
            'description' => $this->faker->sentence(12),
            'start_latitude' => $this->faker->latitude(-30, 5),
            'start_longitude' => $this->faker->longitude(-75, -35),
            'created_by' => User::factory(),
        ];
    }
}
