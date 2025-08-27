<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LoyaltyService
{
    const LEVELS = [
        'bronze' => ['min_points' => 0, 'cashback' => 0.01, 'name' => 'Bronze'],
        'silver' => ['min_points' => 1000, 'cashback' => 0.015, 'name' => 'Silver'],
        'gold' => ['min_points' => 5000, 'cashback' => 0.02, 'name' => 'Gold'],
        'platinum' => ['min_points' => 15000, 'cashback' => 0.025, 'name' => 'Platinum'],
        'diamond' => ['min_points' => 50000, 'cashback' => 0.03, 'name' => 'Diamond'],
    ];
    
    const POINT_RATES = [
        'order_complete' => 10, // points per 1000 IDR spent
        'referral_signup' => 500,
        'referral_first_order' => 1000,
        'daily_login' => 5,
        'review_bonus' => 50,
        'birthday_bonus' => 1000,
    ];
    
    public function awardOrderPoints(Order $order): int
    {
        if ($order->status !== 'DELIVERED') {
            return 0;
        }
        
        $user = $order->user;
        $points = floor($order->total / 1000) * self::POINT_RATES['order_complete'];
        
        // Level multiplier
        $level = $user->level ?? 'bronze';
        $multiplier = match($level) {
            'silver' => 1.1,
            'gold' => 1.2,
            'platinum' => 1.3,
            'diamond' => 1.5,
            default => 1.0
        };
        
        $points = (int) floor($points * $multiplier);
        
        if ($points > 0) {
            $this->addPoints($user, $points, "Order #{$order->invoice_no} completed");
        }
        
        return $points;
    }
    
    public function awardReferralPoints(User $referrer, User $newUser, string $type = 'signup'): int
    {
        $points = self::POINT_RATES["referral_$type"] ?? 0;
        
        if ($points > 0) {
            $this->addPoints($referrer, $points, "Referral bonus: {$newUser->name} {$type}");
        }
        
        return $points;
    }
    
    public function awardDailyLoginPoints(User $user): int
    {
        // Check if user already got login points today
        $today = now()->format('Y-m-d');
        $lastLogin = $user->last_login_at ? $user->last_login_at->format('Y-m-d') : null;
        
        if ($lastLogin === $today) {
            return 0; // Already got points today
        }
        
        $points = self::POINT_RATES['daily_login'];
        $this->addPoints($user, $points, 'Daily login bonus');
        
        return $points;
    }
    
    public function awardReviewPoints(User $user, $reviewId): int
    {
        $points = self::POINT_RATES['review_bonus'];
        $this->addPoints($user, $points, "Review bonus for review #{$reviewId}");
        
        return $points;
    }
    
    public function awardBirthdayPoints(User $user): int
    {
        $points = self::POINT_RATES['birthday_bonus'];
        $this->addPoints($user, $points, 'Happy Birthday bonus!');
        
        return $points;
    }
    
    public function addPoints(User $user, int $points, string $description = ''): bool
    {
        try {
            DB::transaction(function () use ($user, $points, $description) {
                $oldPoints = $user->loyalty_points ?? 0;
                $newPoints = $oldPoints + $points;
                
                $user->update(['loyalty_points' => $newPoints]);
                
                // Check for level upgrade
                $this->checkLevelUpgrade($user, $oldPoints, $newPoints);
                
                // Log the transaction
                $this->logPointTransaction($user, $points, $description, 'earned');
            });
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to add loyalty points', [
                'user_id' => $user->id,
                'points' => $points,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    public function redeemPoints(User $user, int $points, string $description = ''): bool
    {
        if ($user->loyalty_points < $points) {
            return false;
        }
        
        try {
            DB::transaction(function () use ($user, $points, $description) {
                $user->decrement('loyalty_points', $points);
                $this->logPointTransaction($user, $points, $description, 'redeemed');
            });
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to redeem loyalty points', [
                'user_id' => $user->id,
                'points' => $points,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    public function checkLevelUpgrade(User $user, int $oldPoints, int $newPoints): ?string
    {
        $oldLevel = $this->getLevelFromPoints($oldPoints);
        $newLevel = $this->getLevelFromPoints($newPoints);
        
        if ($oldLevel !== $newLevel) {
            $user->update(['level' => $newLevel]);
            
            // Award upgrade bonus
            $upgradeBonus = $this->getLevelUpgradeBonus($newLevel);
            if ($upgradeBonus > 0) {
                $user->increment('loyalty_points', $upgradeBonus);
                $this->logPointTransaction($user, $upgradeBonus, "Level upgrade to {$newLevel}", 'bonus');
            }
            
            Log::info('User level upgraded', [
                'user_id' => $user->id,
                'old_level' => $oldLevel,
                'new_level' => $newLevel,
                'bonus_points' => $upgradeBonus
            ]);
            
            return $newLevel;
        }
        
        return null;
    }
    
    public function getLevelFromPoints(int $points): string
    {
        foreach (array_reverse(self::LEVELS, true) as $level => $data) {
            if ($points >= $data['min_points']) {
                return $level;
            }
        }
        return 'bronze';
    }
    
    public function getLevelUpgradeBonus(string $level): int
    {
        return match($level) {
            'silver' => 100,
            'gold' => 250,
            'platinum' => 500,
            'diamond' => 1000,
            default => 0
        };
    }
    
    public function getCashbackRate(User $user): float
    {
        $level = $user->level ?? 'bronze';
        return self::LEVELS[$level]['cashback'] ?? 0.01;
    }
    
    public function calculateCashback(User $user, float $amount): float
    {
        return $amount * $this->getCashbackRate($user);
    }
    
    public function getNextLevel(User $user): ?array
    {
        $currentLevel = $user->level ?? 'bronze';
        $currentPoints = $user->loyalty_points ?? 0;
        
        foreach (self::LEVELS as $level => $data) {
            if ($data['min_points'] > $currentPoints) {
                return [
                    'level' => $level,
                    'name' => $data['name'],
                    'min_points' => $data['min_points'],
                    'points_needed' => $data['min_points'] - $currentPoints,
                    'progress' => $currentPoints / $data['min_points'] * 100
                ];
            }
        }
        
        return null; // Already at max level
    }
    
    private function logPointTransaction(User $user, int $points, string $description, string $type): void
    {
        // Could create a separate LoyaltyTransaction model to log all point transactions
        Log::info('Loyalty points transaction', [
            'user_id' => $user->id,
            'points' => $points,
            'type' => $type,
            'description' => $description,
            'balance_after' => $user->loyalty_points
        ]);
    }
}