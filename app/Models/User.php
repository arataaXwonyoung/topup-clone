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
        'phone_verified_at',
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
        'loyalty_points',
        'level',
        'is_active',
        'is_suspended',
        'suspended_until',
        'suspension_reason',
        'daily_limit',
        'monthly_limit',
        'max_orders_per_day',
        'notes',
        'is_verified',
        'referral_code',
        'referred_by',
        'preferences',
        'notification_preferences',
        'last_login_at',
        'last_login_ip',
        'login_count',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'last_login_date',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
        'is_active' => 'boolean',
        'is_suspended' => 'boolean',
        'suspended_until' => 'datetime',
        'daily_limit' => 'decimal:2',
        'monthly_limit' => 'decimal:2',
        'max_orders_per_day' => 'integer',
        'is_verified' => 'boolean',
        'balance' => 'decimal:2',
        'points' => 'integer',
        'loyalty_points' => 'integer',
        'date_of_birth' => 'date',
        'preferences' => 'array',
        'notification_preferences' => 'array',
        'last_login_at' => 'datetime',
        'login_count' => 'integer',
        'two_factor_enabled' => 'boolean',
        'two_factor_confirmed_at' => 'datetime',
        'two_factor_recovery_codes' => 'array',
    ];

    /**
     * Determine if user can access Filament panel
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin === true && $this->is_active === true && !$this->is_suspended;
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
    /**
     * Check if user account is active
     */
    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    /**
     * Relationships
     */
    public function orders()
    {
        return $this->hasMany(\App\Models\Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(\App\Models\Review::class);
    }

    public function supportTickets()
    {
        return $this->hasMany(\App\Models\SupportTicket::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function wishlistedGames()
    {
        return $this->belongsToMany(Game::class, 'wishlists');
    }

    public function referredUsers()
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    // Gamification Relationships
    public function userPoints()
    {
        return $this->hasOne(UserPoint::class);
    }

    public function pointTransactions()
    {
        return $this->hasMany(PointTransaction::class);
    }

    public function achievements()
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements')
            ->withPivot(['progress', 'is_unlocked', 'unlocked_at'])
            ->withTimestamps();
    }

    public function unlockedAchievements()
    {
        return $this->achievements()->wherePivot('is_unlocked', true);
    }

    public function gamingProfile()
    {
        return $this->hasOne(UserGamingProfile::class);
    }

    public function rewardRedemptions()
    {
        return $this->hasMany(RewardRedemption::class);
    }

    public function referrals()
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    // 2FA Methods
    public function generateReferralCode()
    {
        if (!$this->referral_code) {
            $this->referral_code = strtoupper(Str::random(8));
            $this->save();
        }
        return $this->referral_code;
    }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_enabled && !empty($this->two_factor_secret);
    }

    public function generateTwoFactorRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(Str::random(6));
        }
        
        $this->two_factor_recovery_codes = $codes;
        $this->save();
        
        return $codes;
    }
}