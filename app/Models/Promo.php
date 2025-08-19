<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'type', 'value', 'min_total', 'max_discount',
        'quota', 'used_count', 'per_user_limit',
        'starts_at', 'ends_at', 'is_active', 'game_ids'
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_total' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'quota' => 'integer',
        'used_count' => 'integer',
        'per_user_limit' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
        'game_ids' => 'array',
    ];

    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at > now()) {
            return false;
        }

        if ($this->ends_at && $this->ends_at < now()) {
            return false;
        }

        if ($this->quota && $this->used_count >= $this->quota) {
            return false;
        }

        return true;
    }

    public function isValidForGame($gameId): bool
    {
        if (!$this->game_ids) {
            return true; // All games
        }

        return in_array($gameId, $this->game_ids);
    }

    public function calculateDiscount($subtotal): float
    {
        if ($subtotal < $this->min_total) {
            return 0;
        }

        $discount = $this->type === 'percent'
            ? $subtotal * ($this->value / 100)
            : $this->value;

        if ($this->max_discount && $discount > $this->max_discount) {
            return $this->max_discount;
        }

        return min($discount, $subtotal);
    }

    public function incrementUsage(): void
    {
        $this->increment('used_count');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }
}