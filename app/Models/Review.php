<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'rating' => 'integer',
        'is_verified' => 'boolean',
        'is_approved' => 'boolean',
        'is_anonymous' => 'boolean',
        'is_flagged' => 'boolean',
        'helpful_count' => 'integer',
        'images' => 'array',
        'helpful_users' => 'array',
        'metadata' => 'array'
    ];

    /**
     * Get the order that owns the review
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user that owns the review
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the game that owns the review
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Get responses to this review
     */
    public function responses(): HasMany
    {
        return $this->hasMany(ReviewResponse::class);
    }

    /**
     * Scope for approved reviews
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope for verified reviews
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope for flagged reviews
     */
    public function scopeFlagged($query)
    {
        return $query->where('is_flagged', true);
    }

    /**
     * Get display name attribute
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->is_anonymous) {
            return 'Anonymous';
        }
        
        return $this->user ? $this->user->name : 'Guest';
    }

    /**
     * Get rating percentage attribute
     */
    public function getRatingPercentageAttribute(): float
    {
        return ($this->rating / 5) * 100;
    }

    /**
     * Get rating stars HTML
     */
    public function getRatingStarsAttribute(): string
    {
        $stars = '';
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $this->rating) {
                $stars .= '<i class="fas fa-star text-yellow-400"></i>';
            } else {
                $stars .= '<i class="far fa-star text-gray-400"></i>';
            }
        }
        return $stars;
    }

    /**
     * Check if user found this helpful
     */
    public function isHelpfulByUser($userId): bool
    {
        $helpfulUsers = $this->helpful_users ?? [];
        return in_array($userId, $helpfulUsers);
    }

    /**
     * Add user to helpful list
     */
    public function markAsHelpfulBy($userId): void
    {
        $helpfulUsers = $this->helpful_users ?? [];
        
        if (!in_array($userId, $helpfulUsers)) {
            $helpfulUsers[] = $userId;
            $this->update([
                'helpful_users' => $helpfulUsers,
                'helpful_count' => count($helpfulUsers)
            ]);
        }
    }

    /**
     * Get formatted date attribute
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Check if review can be edited
     */
    public function canBeEditedBy($userId): bool
    {
        return $this->user_id === $userId && !$this->is_approved;
    }

    /**
     * Check if review has images
     */
    public function hasImages(): bool
    {
        return !empty($this->images);
    }

    /**
     * Get image URLs
     */
    public function getImageUrlsAttribute(): array
    {
        if (!$this->images) {
            return [];
        }
        
        return array_map(function ($image) {
            return asset('storage/' . $image);
        }, $this->images);
    }
}