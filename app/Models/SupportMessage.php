<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
        'attachments',
        'is_staff',
        'is_system',
        'is_read',
        'read_at'
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_staff' => 'boolean',
        'is_system' => 'boolean',
        'is_read' => 'boolean',
        'read_at' => 'datetime'
    ];

    /**
     * Get the ticket that owns the message
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    /**
     * Get the user who sent the message
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark message as read
     */
    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now()
            ]);
        }
    }

    /**
     * Get sender name attribute
     */
    public function getSenderNameAttribute(): string
    {
        if ($this->is_system) {
            return 'System';
        }
        
        if ($this->is_staff) {
            return $this->user->name . ' (Support)';
        }
        
        return $this->user->name;
    }

    /**
     * Get sender avatar attribute
     */
    public function getSenderAvatarAttribute(): string
    {
        if ($this->is_system) {
            return asset('images/system-avatar.png');
        }
        
        return $this->user->getFilamentAvatarUrl() ?? asset('images/default-avatar.png');
    }

    /**
     * Check if message has attachments
     */
    public function hasAttachments(): bool
    {
        return !empty($this->attachments);
    }

    /**
     * Get attachment count
     */
    public function getAttachmentCountAttribute(): int
    {
        return count($this->attachments ?? []);
    }

    /**
     * Get total attachment size
     */
    public function getTotalAttachmentSizeAttribute(): int
    {
        if (!$this->attachments) {
            return 0;
        }
        
        return array_sum(array_column($this->attachments, 'size'));
    }

    /**
     * Get formatted attachment size
     */
    public function getFormattedAttachmentSizeAttribute(): string
    {
        $bytes = $this->total_attachment_size;
        
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}