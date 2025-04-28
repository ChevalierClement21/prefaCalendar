<?php

namespace Database\Factories;

use App\Models\Tour;
use App\Models\TourCompletion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TourCompletion>
 */
class TourCompletionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TourCompletion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkCount = fake()->numberBetween(0, 5);
        $checkAmounts = [];
        $checkTotalAmount = 0;
        
        for ($i = 0; $i < $checkCount; $i++) {
            $amount = fake()->randomFloat(2, 5, 50);
            $checkAmounts[] = $amount;
            $checkTotalAmount += $amount;
        }
        
        $tickets_5 = fake()->numberBetween(0, 5);
        $tickets_10 = fake()->numberBetween(0, 5);
        $tickets_20 = fake()->numberBetween(0, 5);
        $tickets_50 = fake()->numberBetween(0, 3);
        $tickets_100 = fake()->numberBetween(0, 2);
        $tickets_200 = fake()->numberBetween(0, 1);
        $tickets_500 = fake()->numberBetween(0, 1);
        
        $coins_1c = fake()->numberBetween(0, 20);
        $coins_2c = fake()->numberBetween(0, 20);
        $coins_5c = fake()->numberBetween(0, 20);
        $coins_10c = fake()->numberBetween(0, 15);
        $coins_20c = fake()->numberBetween(0, 10);
        $coins_50c = fake()->numberBetween(0, 10);
        $coins_1e = fake()->numberBetween(0, 5);
        $coins_2e = fake()->numberBetween(0, 5);
        
        $billsTotal = 
            ($tickets_5 * 5) +
            ($tickets_10 * 10) +
            ($tickets_20 * 20) +
            ($tickets_50 * 50) +
            ($tickets_100 * 100) +
            ($tickets_200 * 200) +
            ($tickets_500 * 500);
        
        $coinsTotal = 
            ($coins_1c * 0.01) +
            ($coins_2c * 0.02) +
            ($coins_5c * 0.05) +
            ($coins_10c * 0.10) +
            ($coins_20c * 0.20) +
            ($coins_50c * 0.50) +
            ($coins_1e * 1) +
            ($coins_2e * 2);
        
        $totalAmount = $billsTotal + $coinsTotal + $checkTotalAmount;
        
        return [
            'tour_id' => Tour::factory(),
            'calendars_sold' => fake()->numberBetween(0, 50),
            
            // Billets
            'tickets_5' => $tickets_5,
            'tickets_10' => $tickets_10,
            'tickets_20' => $tickets_20,
            'tickets_50' => $tickets_50,
            'tickets_100' => $tickets_100,
            'tickets_200' => $tickets_200,
            'tickets_500' => $tickets_500,
            
            // Pièces
            'coins_1c' => $coins_1c,
            'coins_2c' => $coins_2c,
            'coins_5c' => $coins_5c,
            'coins_10c' => $coins_10c,
            'coins_20c' => $coins_20c,
            'coins_50c' => $coins_50c,
            'coins_1e' => $coins_1e,
            'coins_2e' => $coins_2e,
            
            // Chèques
            'check_count' => $checkCount,
            'check_total_amount' => $checkTotalAmount,
            'check_amounts' => $checkAmounts,
            
            'total_amount' => $totalAmount,
            'notes' => fake()->optional(0.7)->sentence(),
        ];
    }
    
    /**
     * Indicate that the completion belongs to a specific tour.
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
}
