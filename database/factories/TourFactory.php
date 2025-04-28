<?php

namespace Database\Factories;

use App\Models\Sector;
use App\Models\Session;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tour>
 */
class TourFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Tour::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word() . ' Tour',
            'session_id' => Session::factory(),
            'sector_id' => null,
            'creator_id' => User::factory(),
            'status' => fake()->randomElement(['in_progress', 'completed']),
            'start_date' => fake()->dateTimeBetween('now', '+1 month'),
            'end_date' => fake()->dateTimeBetween('+1 month', '+2 months'),
            'notes' => fake()->optional(0.7)->paragraph(),
        ];
    }

    /**
     * Indicate that the tour belongs to a specific session.
     *
     * @param int|Session $session
     * @return $this
     */
    public function forSession($session): static
    {
        $sessionId = $session instanceof Session ? $session->id : $session;
        
        return $this->state(fn (array $attributes) => [
            'session_id' => $sessionId,
        ]);
    }

    /**
     * Indicate that the tour belongs to a specific sector.
     *
     * @param int|Sector $sector
     * @return $this
     */
    public function forSector($sector): static
    {
        $sectorId = $sector instanceof Sector ? $sector->id : $sector;
        
        return $this->state(fn (array $attributes) => [
            'sector_id' => $sectorId,
        ]);
    }

    /**
     * Indicate that the tour was created by a specific user.
     *
     * @param int|User $creator
     * @return $this
     */
    public function createdBy($creator): static
    {
        $creatorId = $creator instanceof User ? $creator->id : $creator;
        
        return $this->state(fn (array $attributes) => [
            'creator_id' => $creatorId,
        ]);
    }
}