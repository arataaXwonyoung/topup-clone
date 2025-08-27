<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'points', 'tier', 'total_earned', 'total_spent'
    ];

    protected $casts = [
        'points' => 'integer',
        'total_earned' => 'integer',
        'total_spent' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PointTransaction::class, 'user_id', 'user_id');
    }

    /**
     * Get tier multiplier for earning points
     */
    public function getTierMultiplierAttribute(): float
    {
        return match($this->tier) {
            'bronze' => 1.0,
            'silver' => 1.2,
            'gold' => 1.5,
            'diamond' => 2.0,
            default => 1.0
        };
    }

    /**
     * Get next tier requirements
     */
    public function getNextTierAttribute(): ?array
    {
        $totalSpent = $this->user->orders()->whereIn('status', ['PAID', 'DELIVERED'])->sum('total');
        
        return match($this->tier) {
            'bronze' => [
                'name' => 'Silver',
                'required' => 1000000,
                'current' => $totalSpent,
                'progress' => min(100, ($totalSpent / 1000000) * 100)
            ],
            'silver' => [
                'name' => 'Gold', 
                'required' => 5000000,
                'current' => $totalSpent,
                'progress' => min(100, ($totalSpent / 5000000) * 100)
            ],
            'gold' => [
                'name' => 'Diamond',
                'required' => 10000000, 
                'current' => $totalSpent,
                'progress' => min(100, ($totalSpent / 10000000) * 100)
            ],
            'diamond' => null // Max tier
        };
    }
}