<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\GamificationService;
use App\Models\Reward;
use App\Models\RewardRedemption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RewardController extends Controller
{
    protected GamificationService $gamificationService;

    public function __construct(GamificationService $gamificationService)
    {
        $this->gamificationService = $gamificationService;
    }

    /**
     * Display available rewards
     */
    public function index()
    {
        $user = auth()->user();
        $userPoints = $user->userPoints ?? null;
        
        // Get user's point balance and tier info
        $currentPoints = $userPoints ? $userPoints->points : 0;
        $tier = $userPoints ? $userPoints->tier : 'bronze';
        $nextTier = $userPoints ? $userPoints->next_tier : null;
        
        // Get available rewards
        $rewards = Reward::where('is_active', true)
            ->orderBy('points_cost', 'asc')
            ->get();
            
        // Get recent redemptions
        $recentRedemptions = $user->rewardRedemptions()
            ->with('reward')
            ->latest()
            ->limit(10)
            ->get();

        return view('user.rewards.index', compact(
            'user', 'rewards', 'currentPoints', 'tier', 'nextTier', 'recentRedemptions'
        ));
    }

    /**
     * Redeem a reward
     */
    public function redeem(Request $request)
    {
        $request->validate([
            'reward_type' => 'required|string',
            'points_cost' => 'required|integer|min:1',
        ]);

        $user = auth()->user();
        
        try {
            DB::transaction(function () use ($user, $request) {
                // Find reward by type
                $reward = Reward::where('type', $request->reward_type)
                    ->where('points_cost', $request->points_cost)
                    ->where('is_active', true)
                    ->first();

                if (!$reward) {
                    throw new \Exception('Reward not found or no longer available');
                }

                // Check stock for limited rewards
                if ($reward->stock !== null && $reward->stock <= 0) {
                    throw new \Exception('Reward is out of stock');
                }

                // Check if user has enough points
                $userPoints = $user->userPoints;
                if (!$userPoints || $userPoints->points < $reward->points_cost) {
                    throw new \Exception('Insufficient points');
                }

                // Deduct points
                $success = $this->gamificationService->spendPoints(
                    $user, 
                    $reward->points_cost, 
                    "Redeemed: {$reward->name}",
                    $reward
                );

                if (!$success) {
                    throw new \Exception('Failed to deduct points');
                }

                // Create redemption record
                $redemptionCode = 'RDM' . time() . rand(1000, 9999);
                
                $redemption = RewardRedemption::create([
                    'user_id' => $user->id,
                    'reward_id' => $reward->id,
                    'points_spent' => $reward->points_cost,
                    'redemption_code' => $redemptionCode,
                    'status' => 'pending',
                    'expires_at' => now()->addDays(30), // Rewards expire in 30 days
                ]);

                // Process delivery based on reward type
                $this->processRewardDelivery($redemption);

                // Decrement stock if limited
                if ($reward->stock !== null) {
                    $reward->decrement('stock');
                }

                Log::info("User {$user->id} redeemed reward {$reward->id} with code {$redemptionCode}");
            });

            return response()->json([
                'success' => true,
                'message' => 'Reward redeemed successfully! Check your email for delivery details.'
            ]);

        } catch (\Exception $e) {
            Log::error("Reward redemption failed for user {$user->id}: {$e->getMessage()}");
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Process reward delivery
     */
    private function processRewardDelivery(RewardRedemption $redemption): void
    {
        $reward = $redemption->reward;
        
        switch ($reward->type) {
            case 'game_credit':
                // Add credit to user account
                $this->deliverGameCredit($redemption);
                break;
                
            case 'voucher':
                // Generate voucher code
                $this->deliverVoucher($redemption);
                break;
                
            case 'exclusive':
                // Process exclusive reward (VIP status, etc.)
                $this->deliverExclusiveReward($redemption);
                break;
                
            default:
                // Mark as delivered for manual processing
                $redemption->update([
                    'status' => 'delivered',
                    'delivered_at' => now(),
                    'delivery_data' => [
                        'note' => 'Reward will be processed manually within 24 hours'
                    ]
                ]);
        }
    }

    /**
     * Deliver game credit reward
     */
    private function deliverGameCredit(RewardRedemption $redemption): void
    {
        $reward = $redemption->reward;
        $user = $redemption->user;
        
        // Add credit to user's balance
        $creditAmount = $reward->value;
        $user->increment('balance', $creditAmount);
        
        // Update redemption
        $redemption->update([
            'status' => 'delivered',
            'delivered_at' => now(),
            'delivery_data' => [
                'credit_amount' => $creditAmount,
                'previous_balance' => $user->balance - $creditAmount,
                'new_balance' => $user->balance
            ]
        ]);
        
        Log::info("Delivered {$creditAmount} game credit to user {$user->id}");
    }

    /**
     * Deliver voucher reward
     */
    private function deliverVoucher(RewardRedemption $redemption): void
    {
        $reward = $redemption->reward;
        $voucherCode = 'VCH' . time() . rand(10000, 99999);
        
        // In real implementation, integrate with voucher provider API
        // For now, generate a voucher code
        
        $redemption->update([
            'status' => 'delivered',
            'delivered_at' => now(),
            'delivery_data' => [
                'voucher_code' => $voucherCode,
                'voucher_value' => $reward->value,
                'voucher_brand' => $reward->metadata['brand'] ?? 'Generic',
                'instructions' => 'Use this code at participating outlets',
                'expires_at' => now()->addMonths(6)->toDateString()
            ]
        ]);
        
        // TODO: Send email with voucher details
        
        Log::info("Delivered voucher {$voucherCode} to user {$redemption->user_id}");
    }

    /**
     * Deliver exclusive reward
     */
    private function deliverExclusiveReward(RewardRedemption $redemption): void
    {
        $reward = $redemption->reward;
        $user = $redemption->user;
        
        switch ($reward->metadata['exclusive_type'] ?? '') {
            case 'vip_status':
                // Grant VIP status
                $user->update([
                    'is_vip' => true,
                    'vip_expires_at' => now()->addDays($reward->metadata['vip_days'] ?? 30)
                ]);
                break;
                
            case 'point_multiplier':
                // Grant point multiplier boost
                $user->userPoints->update([
                    'multiplier_boost' => $reward->metadata['multiplier'] ?? 1.5,
                    'multiplier_expires_at' => now()->addDays($reward->metadata['boost_days'] ?? 7)
                ]);
                break;
        }
        
        $redemption->update([
            'status' => 'delivered',
            'delivered_at' => now(),
            'delivery_data' => $reward->metadata
        ]);
        
        Log::info("Delivered exclusive reward to user {$user->id}");
    }

    /**
     * Get user's redemption history
     */
    public function history()
    {
        $user = auth()->user();
        
        $redemptions = $user->rewardRedemptions()
            ->with('reward')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('user.rewards.history', compact('redemptions'));
    }
}