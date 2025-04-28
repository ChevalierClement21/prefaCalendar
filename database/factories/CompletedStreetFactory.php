<?php

namespace Database\Factories;

use App\Models\CompletedStreet;
use App\Models\Session;
use App\Models\Street;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CompletedStreet>
 */
class CompletedStreetFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CompletedStreet::class;

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
            'session_id' => Session::factory(),
            'completed_by' => User::factory(),
            'notes' => fake()->optional(0.5)->sentence(),
        ];
    }
    
    /**
     * Indicate that the completed street belongs to a specific tour.
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
     * Indicate that the completed street record is for a specific street.
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
     * Indicate that the completed street record is for a specific session.
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
     * Indicate that the street was completed by a specific user.
     *
     * @param int|User $user
     * @return $this
     */
    public function completedBy($user): static
    {
        $userId = $user instanceof User ? $user->id : $user;
        
        return $this->state(fn (array $attributes) => [
            'completed_by' => $userId,
        ]);
    }
}
