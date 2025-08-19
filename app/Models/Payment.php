<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'provider', 'method', 'channel', 'reference',
        'external_id', 'va_number', 'qris_string', 'checkout_url',
        'status', 'amount', 'fee', 'payload', 'expires_at', 'paid_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'payload' => 'array',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    public function isPaid(): bool
    {
        return $this->status === 'PAID';
    }

    public function isExpired(): bool
    {
        return $this->status === 'EXPIRED' || 
               ($this->isPending() && $this->expires_at < now());
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'PAID',
            'paid_at' => now(),
        ]);
    }

    public function markAsExpired(): void
    {
        $this->update(['status' => 'EXPIRED']);
    }
}