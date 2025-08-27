<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'is_active',
        'priority',
        'base_url',
        'api_key',
        'secret_key',
        'username',
        'password',
        'webhook_url',
        'configuration',
        'supported_methods',
        'rate_limit',
        'timeout',
        'retry_attempts',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'configuration' => 'array',
        'supported_methods' => 'array',
        'rate_limit' => 'integer',
        'timeout' => 'integer',
        'retry_attempts' => 'integer',
        'metadata' => 'array',
    ];

    protected $hidden = [
        'api_key',
        'secret_key',
        'password',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function getStatusAttribute(): string
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    public function hasMethod(string $method): bool
    {
        return in_array($method, $this->supported_methods ?? []);
    }

    public function getConfigValue(string $key, $default = null)
    {
        return $this->configuration[$key] ?? $default;
    }

    public function setConfigValue(string $key, $value): void
    {
        $config = $this->configuration ?? [];
        $config[$key] = $value;
        $this->configuration = $config;
    }
}