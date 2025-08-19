<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'phone',
        'whatsapp',
        'date_of_birth',
        'gender',
        'address',
        'city',
        'province',
        'postal_code',
        'country',
        'avatar',
        'balance',
        'points',
        'level',
        'is_active',
        'is_verified',
        'referral_code',
        'referred_by',
        'preferences',
        'last_login_at',
        'last_login_ip',
        'login_count',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'balance' => 'decimal:2',
        'points' => 'integer',
        'date_of_birth' => 'date',
        'preferences' => 'array',
        'last_login_at' => 'datetime',
        'login_count' => 'integer',
    ];

    /**
     * Determine if user can access Filament panel
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin === true && $this->is_active === true;
    }

    /**
     * Get the user's name for Filament
     */
    public function getFilamentName(): string
    {
        return $this->name;
    }

    /**
     * Get avatar URL for Filament
     */
    public function getFilamentAvatarUrl(): ?string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=FFD700&color=1a1a1a';
    }

    // ... rest of the model code .

    public function getFormattedBalanceAttribute()
    {
        return 'Rp ' . number_format($this->balance, 0, ',', '.');
    }

    public function getFormattedPointsAttribute()
    {
        return number_format($this->points, 0, ',', '.');
    }

    /**
     * Methods
     */
    public function addBalance($amount)
    {
        $this->increment('balance', $amount);
        return $this;
    }

    public function deductBalance($amount)
    {
        if ($this->balance < $amount) {
            throw new \Exception('Insufficient balance');
        }
        
        $this->decrement('balance', $amount);
        return $this;
    }

    public function addPoints($points)
    {
        $this->increment('points', $points);
        $this->checkAndUpdateLevel();
        return $this;
    }

    public function checkAndUpdateLevel()
    {
        $levels = [
            'bronze' => 0,
            'silver' => 1000,
            'gold' => 5000,
            'platinum' => 10000,
            'diamond' => 50000,
        ];

        foreach (array_reverse($levels, true) as $level => $minPoints) {
            if ($this->points >= $minPoints) {
                $this->level = $level;
                $this->save();
                break;
            }
        }
    }

    public function recordLogin($ip = null)
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip ?? request()->ip(),
            'login_count' => $this->login_count + 1,
        ]);
    }

    public function getTotalSpentAttribute()
    {
        return $this->orders()
            ->whereIn('status', ['PAID', 'DELIVERED'])
            ->sum('total');
    }

    public function getOrderCountAttribute()
    {
        return $this->orders()
            ->whereIn('status', ['PAID', 'DELIVERED'])
            ->count();
    }

    /**
     * Check if user can perform admin actions
     */
    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }

    /**
     * Check if user account is active
     */
    public function isActive(): bool
    {
        return $this->is_active === true;
    }
}