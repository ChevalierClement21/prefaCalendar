<?php

namespace Database\Factories;

use App\Models\HouseNumber;
use App\Models\Street;
use App\Models\Tour;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HouseNumber>
 */
class HouseNumberFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = HouseNumber::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tour_id' => Tour::factory(),
            'street_id' => Street::factory(),
            'number' => fake()->numberBetween(1, 200),
            'status' => fake()->randomElement(['to_revisit', 'visited', 'skipped']),
            'notes' => fake()->optional(0.7)->sentence(),
        ];
    }

    /**
     * Indicate that the house number belongs to a specific tour.
     *
     * @param int|Tour $tour
     * @return $this
     */
    public function forTour($tour): static
    {
        $tourId = $tour instanceof Tour ? $tour->id : $tour;
        
        return $this->state(fn (array $attributes) => [
            'tour_id' => $tourId,
        ]);
    }

    /**
     * Indicate that the house number belongs to a specific street.
     *
     * @param int|Street $street
     * @return $this
     */
    public function forStreet($street): static
    {
        $streetId = $street instanceof Street ? $street->id : $street;
        
        return $this->state(fn (array $attributes) => [
            'street_id' => $streetId,
        ]);
    }

    /**
     * Indicate that the house number has a specific status.
     *
     * @param string $status
     * @return $this
     */
    public function withStatus(string $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }
}
