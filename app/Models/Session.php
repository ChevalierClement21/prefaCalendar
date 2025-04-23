<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Session extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::creating(function ($session) {
            // Définir l'année actuelle si non définie
            if (!$session->year) {
                $session->year = date('Y');
            }
        });
    }

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'calendar_sessions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'year',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'year' => 'integer',
    ];

    /**
     * Get the tours for the session.
     */
    public function tours(): HasMany
    {
        return $this->hasMany(Tour::class);
    }

    /**
     * Get the active session.
     *
     * @return Session|null
     */
    public static function getActive()
    {
        return self::where('is_active', true)->first();
    }

    /**
     * Set this session as the only active one.
     *
     * @return bool
     */
    public function setAsActive(): bool
    {
        // First deactivate all sessions
        self::query()->update(['is_active' => false]);
        
        // Then activate this one
        $this->is_active = true;
        return $this->save();
    }
}
