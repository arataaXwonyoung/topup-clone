<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'publisher', 'cover_path', 'description',
        'category', 'is_hot', 'is_active', 'sort_order',
        'requires_server', 'id_label', 'server_label', 'metadata'
    ];

    protected $casts = [
        'is_hot' => 'boolean',
        'is_active' => 'boolean',
        'requires_server' => 'boolean',
        'metadata' => 'array',
    ];

    public function denominations(): HasMany
    {
        return $this->hasMany(Denomination::class)->orderBy('sort_order');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function getAverageRatingAttribute(): float
    {
        return $this->reviews()
            ->where('is_approved', true)
            ->avg('rating') ?? 0;
    }

    public function getReviewCountAttribute(): int
    {
        return $this->reviews()
            ->where('is_approved', true)
            ->count();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}