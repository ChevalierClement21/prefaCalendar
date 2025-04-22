<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourCompletion extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tour_id',
        'calendars_sold',
        'tickets_5',
        'tickets_10',
        'tickets_20',
        'tickets_50',
        'tickets_100',
        'tickets_200',
        'tickets_500',
        'coins_1c',
        'coins_2c',
        'coins_5c',
        'coins_10c',
        'coins_20c',
        'coins_50c',
        'coins_1e',
        'coins_2e',
        'check_count',
        'check_total_amount',
        'check_amounts',
        'total_amount',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'check_amounts' => 'array',
        'check_total_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Get the tour associated with the completion.
     */
    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    /**
     * Calculate the total amount from bills, coins, and checks.
     */
    public function calculateTotal(): void
    {
        $billsTotal = 
            ($this->tickets_5 * 5) +
            ($this->tickets_10 * 10) +
            ($this->tickets_20 * 20) +
            ($this->tickets_50 * 50) +
            ($this->tickets_100 * 100) +
            ($this->tickets_200 * 200) +
            ($this->tickets_500 * 500);
        
        $coinsTotal = 
            ($this->coins_1c * 0.01) +
            ($this->coins_2c * 0.02) +
            ($this->coins_5c * 0.05) +
            ($this->coins_10c * 0.10) +
            ($this->coins_20c * 0.20) +
            ($this->coins_50c * 0.50) +
            ($this->coins_1e * 1) +
            ($this->coins_2e * 2);
        
        $this->total_amount = $billsTotal + $coinsTotal + $this->check_total_amount;
    }
}