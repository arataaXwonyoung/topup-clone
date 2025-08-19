<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider', 'reference', 'event_type', 'signature',
        'headers', 'raw_payload', 'processed_at', 'status', 'error_message'
    ];

    protected $casts = [
        'headers' => 'array',
        'raw_payload' => 'array',
        'processed_at' => 'datetime',
    ];
}