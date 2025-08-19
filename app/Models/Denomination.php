<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Denomination extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id', 'name', 'amount', 'bonus', 'price', 'original_price',
        'is_hot', 'is_promo', 'is_active', 'sort_order', 'sku', 'metadata'
    ];

    protected $casts = [
        'amount' => 'integer',
        'bonus' => 'integer',
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'is_hot' => 'boolean',
        'is_promo' => 'boolean',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function getTotalAmountAttribute(): int
    {
        return $this->amount + $this->bonus;
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}