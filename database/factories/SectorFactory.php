<?php

namespace Database\Factories;

use App\Models\Sector;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sector>
 */
class SectorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Sector::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'description' => $this->faker->optional()->sentence(),
            'color' => $this->faker->optional()->hexColor(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
