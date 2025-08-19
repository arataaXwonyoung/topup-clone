<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no', 'user_id', 'game_id', 'denomination_id',
        'account_id', 'server_id', 'username', 'email', 'whatsapp',
        'quantity', 'subtotal', 'discount', 'promo_code', 'fee', 'total',
        'status', 'expires_at', 'paid_at', 'delivered_at',
        'delivery_data', 'metadata'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'fee' => 'decimal:2',
        'total' => 'decimal:2',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
        'delivered_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (!$order->invoice_no) {
                $order->invoice_no = static::generateInvoiceNumber();
            }
            if (!$order->expires_at) {
                $order->expires_at = now()->addHours(3);
            }
        });
    }

    public static function generateInvoiceNumber(): string
    {
        do {
            $invoice = 'TP' . strtoupper(Str::random(4)) . date('YmdHis');
        } while (static::where('invoice_no', $invoice)->exists());

        return $invoice;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function denomination(): BelongsTo
    {
        return $this->belongsTo(Denomination::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function isPending(): bool
    {
        return in_array($this->status, ['PENDING', 'UNPAID']);
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

    public function canBePaid(): bool
    {
        return $this->isPending() && !$this->isExpired();
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

    public function markAsDelivered($deliveryData = null): void
    {
        $this->update([
            'status' => 'DELIVERED',
            'delivered_at' => now(),
            'delivery_data' => $deliveryData,
        ]);
    }
}