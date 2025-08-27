<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\UserPoint;
use App\Models\PointTransaction;
use App\Models\UserAchievement;
use App\Models\Achievement;
use App\Models\UserGamingProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GamificationService
{
    /**
     * Process gamification when user completes an order
     */
    public function processOrderCompletion(Order $order): void
    {
        try {
            DB::transaction(function () use ($order) {
                $user = $order->user;
                
                // 1. Award points for purchase
                $this->awardPurchasePoints($user, $order);
                
                // 2. Update gaming profile
                $this->updateGamingProfile($user, $order);
                
                // 3. Check and unlock achievements
                $this->checkAchievements($user);
                
                // 4. Update user tier if eligible
                $this->updateUserTier($user);
                
                // 5. Process streaks
                $this->updateStreaks($user);
                
                Log::info("Gamification processed for order {$order->id}");
            });
        } catch (\Exception $e) {
            Log::error("Gamification processing failed for order {$order->id}: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Award points for purchase with tier multiplier
     */
    private function awardPurchasePoints(User $user, Order $order): void
    {
        $basePoints = (int) floor($order->total / 1000); // 1 point per Rp 1,000
        $tierMultiplier = $this->getTierMultiplier($user->userPoints->tier ?? 'bronze');
        $totalPoints = (int) floor($basePoints * $tierMultiplier);

        $this->addPoints($user, $totalPoints, 'purchase', "Purchase order #{$order->invoice_no}", $order);
    }

    /**
     * Add points to user account
     */
    public function addPoints(User $user, int $points, string $source, string $description, $related = null): void
    {
        // Get or create user points record
        $userPoints = UserPoint::firstOrCreate(
            ['user_id' => $user->id],
            ['points' => 0, 'tier' => 'bronze']
        );

        // Update points
        $userPoints->increment('points', $points);
        $userPoints->increment('total_earned', $points);

        // Record transaction
        PointTransaction::create([
            'user_id' => $user->id,
            'type' => 'earned',
            'amount' => $points,
            'source' => $source,
            'description' => $description,
            'related_type' => $related ? get_class($related) : null,
            'related_id' => $related ? $related->id : null,
        ]);

        Log::info("Added {$points} points to user {$user->id} for {$source}");
    }

    /**
     * Spend points for reward redemption
     */
    public function spendPoints(User $user, int $points, string $description, $related = null): bool
    {
        $userPoints = $user->userPoints;
        
        if (!$userPoints || $userPoints->points < $points) {
            return false;
        }

        $userPoints->decrement('points', $points);
        $userPoints->increment('total_spent', $points);

        PointTransaction::create([
            'user_id' => $user->id,
            'type' => 'spent',
            'amount' => $points,
            'source' => 'reward_redeem',
            'description' => $description,
            'related_type' => $related ? get_class($related) : null,
            'related_id' => $related ? $related->id : null,
        ]);

        return true;
    }

    /**
     * Update gaming profile statistics
     */
    private function updateGamingProfile(User $user, Order $order): void
    {
        $profile = UserGamingProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'gaming_level' => 1,
                'gaming_score' => 0,
                'experience_points' => 0,
                'favorite_games' => [],
                'activity_patterns' => []
            ]
        );

        // Update basic stats
        $profile->increment('total_orders');
        $profile->increment('total_spent', $order->total);

        // Update experience points (10 XP per Rp 1,000 spent)
        $xpGained = (int) floor($order->total / 1000) * 10;
        $profile->increment('experience_points', $xpGained);

        // Calculate gaming level (every 1000 XP = 1 level)
        $newLevel = (int) floor($profile->experience_points / 1000) + 1;
        if ($newLevel > $profile->gaming_level) {
            $profile->gaming_level = $newLevel;
            $this->addPoints($user, 100, 'level_up', "Reached gaming level {$newLevel}");
        }

        // Update favorite games
        $favoriteGames = $profile->favorite_games ?? [];
        $gameId = $order->game_id;
        
        if (!isset($favoriteGames[$gameId])) {
            $favoriteGames[$gameId] = [
                'game_name' => $order->game->name,
                'orders' => 0,
                'total_spent' => 0
            ];
        }
        
        $favoriteGames[$gameId]['orders']++;
        $favoriteGames[$gameId]['total_spent'] += $order->total;
        
        // Count unique games
        $profile->unique_games = count($favoriteGames);
        $profile->favorite_games = $favoriteGames;

        // Update activity patterns
        $activityPatterns = $profile->activity_patterns ?? [];
        $hour = now()->hour;
        $dayOfWeek = now()->dayOfWeek;
        
        $activityPatterns['hours'][$hour] = ($activityPatterns['hours'][$hour] ?? 0) + 1;
        $activityPatterns['days'][$dayOfWeek] = ($activityPatterns['days'][$dayOfWeek] ?? 0) + 1;
        $profile->activity_patterns = $activityPatterns;

        $profile->save();
    }

    /**
     * Check and unlock achievements
     */
    private function checkAchievements(User $user): void
    {
        $achievements = Achievement::where('is_active', true)->get();
        
        foreach ($achievements as $achievement) {
            $userAchievement = UserAchievement::firstOrCreate([
                'user_id' => $user->id,
                'achievement_id' => $achievement->id
            ], [
                'progress' => [],
                'is_unlocked' => false
            ]);

            if ($userAchievement->is_unlocked) {
                continue;
            }

            if ($this->checkAchievementCondition($user, $achievement, $userAchievement)) {
                $this->unlockAchievement($user, $achievement, $userAchievement);
            }
        }
    }

    /**
     * Check if achievement condition is met
     */
    private function checkAchievementCondition(User $user, Achievement $achievement, UserAchievement $userAchievement): bool
    {
        $conditions = $achievement->conditions;
        
        switch ($achievement->key) {
            case 'first_purchase':
                return $user->orders()->whereIn('status', ['PAID', 'DELIVERED'])->count() >= 1;
                
            case 'big_spender':
                return $user->orders()->whereIn('status', ['PAID', 'DELIVERED'])->sum('total') >= 1000000;
                
            case 'million_club':
                return $user->orders()->whereIn('status', ['PAID', 'DELIVERED'])->sum('total') >= 5000000;
                
            case 'loyal_customer':
                return $user->orders()->whereIn('status', ['PAID', 'DELIVERED'])->count() >= 10;
                
            case 'gold_member':
                return ($user->userPoints->tier ?? 'bronze') === 'gold';
                
            case 'game_explorer':
                return $user->gamingProfile->unique_games >= 5;
                
            case 'reviewer':
                return $user->reviews()->count() >= 5;
                
            case 'referral_master':
                return $user->referrals()->where('status', 'completed')->count() >= 3;
                
            case 'hot_streak':
                return $user->gamingProfile->streak_days >= 7;
                
            default:
                return false;
        }
    }

    /**
     * Unlock achievement and award points
     */
    private function unlockAchievement(User $user, Achievement $achievement, UserAchievement $userAchievement): void
    {
        $userAchievement->update([
            'is_unlocked' => true,
            'unlocked_at' => now()
        ]);

        // Award points for achievement
        if ($achievement->points_reward > 0) {
            $this->addPoints(
                $user, 
                $achievement->points_reward, 
                'achievement', 
                "Unlocked achievement: {$achievement->name}",
                $achievement
            );
        }

        // TODO: Send notification to user
        Log::info("User {$user->id} unlocked achievement: {$achievement->name}");
    }

    /**
     * Update user tier based on total spending
     */
    private function updateUserTier(User $user): void
    {
        $userPoints = $user->userPoints;
        if (!$userPoints) return;

        $totalSpent = $user->orders()->whereIn('status', ['PAID', 'DELIVERED'])->sum('total');
        $currentTier = $userPoints->tier;
        $newTier = $this->calculateTier($totalSpent);

        if ($newTier !== $currentTier) {
            $userPoints->tier = $newTier;
            $userPoints->save();

            // Award tier upgrade bonus
            $bonusPoints = $this->getTierBonusPoints($newTier);
            $this->addPoints($user, $bonusPoints, 'tier_upgrade', "Upgraded to {$newTier} tier");

            Log::info("User {$user->id} upgraded to {$newTier} tier");
        }
    }

    /**
     * Update activity streaks
     */
    private function updateStreaks(User $user): void
    {
        $profile = $user->gamingProfile;
        if (!$profile) return;

        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        if ($profile->last_activity_date === $yesterday) {
            // Continue streak
            $profile->increment('streak_days');
        } elseif ($profile->last_activity_date !== $today) {
            // Reset streak
            $profile->streak_days = 1;
        }

        $profile->last_activity_date = $today;
        $profile->save();

        // Award streak bonus
        if ($profile->streak_days % 7 === 0) { // Weekly streak bonus
            $this->addPoints($user, 100, 'streak_bonus', "Weekly streak bonus ({$profile->streak_days} days)");
        }
    }

    /**
     * Calculate tier based on total spending
     */
    private function calculateTier(float $totalSpent): string
    {
        if ($totalSpent >= 10000000) return 'diamond';  // 10M+
        if ($totalSpent >= 5000000) return 'gold';      // 5M+
        if ($totalSpent >= 1000000) return 'silver';    // 1M+
        return 'bronze';
    }

    /**
     * Get tier multiplier for points
     */
    private function getTierMultiplier(string $tier): float
    {
        return match($tier) {
            'bronze' => 1.0,
            'silver' => 1.2,
            'gold' => 1.5,
            'diamond' => 2.0,
            default => 1.0
        };
    }

    /**
     * Get tier upgrade bonus points
     */
    private function getTierBonusPoints(string $tier): int
    {
        return match($tier) {
            'silver' => 500,
            'gold' => 1000,
            'diamond' => 2000,
            default => 0
        };
    }

    /**
     * Process daily login bonus
     */
    public function processDailyLogin(User $user): int
    {
        $today = now()->toDateString();
        $lastLogin = $user->last_login_date ?? null;

        if ($lastLogin === $today) {
            return 0; // Already claimed today
        }

        // Base daily bonus: 10-50 points
        $baseBonus = rand(10, 50);
        $streakBonus = min($user->gamingProfile->streak_days ?? 0, 10) * 5; // Max 50 bonus
        $totalBonus = $baseBonus + $streakBonus;

        $this->addPoints($user, $totalBonus, 'daily_login', 'Daily login bonus');

        // Update last login
        $user->update(['last_login_date' => $today]);

        return $totalBonus;
    }
}