<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'game_id',
        'rating',
        'comment',
        'images',
        'is_verified',
        'is_approved',
        'is_anonymous',
        'helpful_count',
        'helpful_users',
        'is_flagged',
        'metadata'
    ];

    protected $casts = [
        'images' => 'array',
        'helpful_users' => 'array',
        'metadata' => 'array',
        'is_verified' => 'boolean',
        'is_approved' => 'boolean',
        'is_anonymous' => 'boolean',
        'is_flagged' => 'boolean',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function getUserNameAttribute(): string
    {
        if ($this->is_anonymous) {
            return 'Anonymous';
        }
        
        return $this->user ? $this->user->name : 'User';
    }
}