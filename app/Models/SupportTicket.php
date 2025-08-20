<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'user_id',
        'order_id',
        'category',
        'subject',
        'status',
        'priority',
        'assigned_to',
        'rating',
        'feedback',
        'metadata',
        'closed_at',
        'closed_by',
        'reopened_at',
        'rated_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'closed_at' => 'datetime',
        'reopened_at' => 'datetime',
        'rated_at' => 'datetime',
        'rating' => 'integer'
    ];

    /**
     * Get the user who created the ticket
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order associated with the ticket
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the staff member assigned to the ticket
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who closed the ticket
     */
    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /**
     * Get all messages for the ticket
     */
    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class, 'ticket_id');
    }

    /**
     * Get the last message for the ticket
     */
    public function lastMessage(): HasOne
    {
        return $this->hasOne(SupportMessage::class, 'ticket_id')->latestOfMany();
    }

    /**
     * Get unread messages count for user
     */
    public function getUnreadCountAttribute(): int
    {
        return $this->messages()
            ->where('is_staff', true)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'open' => 'bg-green-500',
            'pending' => 'bg-yellow-500',
            'resolved' => 'bg-blue-500',
            'closed' => 'bg-gray-500',
            default => 'bg-gray-500'
        };
    }

    /**
     * Get priority badge color
     */
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'low' => 'bg-gray-500',
            'normal' => 'bg-blue-500',
            'high' => 'bg-orange-500',
            'urgent' => 'bg-red-500',
            default => 'bg-gray-500'
        };
    }

    /**
     * Get category label
     */
    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'payment' => 'Masalah Pembayaran',
            'delivery' => 'Masalah Pengiriman',
            'account' => 'Masalah Akun',
            'refund' => 'Permintaan Refund',
            'technical' => 'Masalah Teknis',
            'other' => 'Lainnya',
            default => 'Lainnya'
        };
    }

    /**
     * Scope for open tickets
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'pending']);
    }

    /**
     * Scope for closed tickets
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    /**
     * Scope for resolved tickets
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    /**
     * Scope for tickets by priority
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Check if ticket can be reopened
     */
    public function canBeReopened(): bool
    {
        return $this->status === 'closed' 
            && $this->closed_at 
            && $this->closed_at->gt(now()->subDays(7));
    }

    /**
     * Check if ticket can be rated
     */
    public function canBeRated(): bool
    {
        return $this->status === 'closed' 
            && !$this->rating 
            && $this->closed_at;
    }
}