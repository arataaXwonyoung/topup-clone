<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ReferralService
{
    protected LoyaltyService $loyaltyService;
    
    public function __construct(LoyaltyService $loyaltyService)
    {
        $this->loyaltyService = $loyaltyService;
    }
    
    public function generateReferralCode(User $user): string
    {
        if ($user->referral_code) {
            return $user->referral_code;
        }
        
        $attempts = 0;
        do {
            $code = $this->createReferralCode($user->name);
            $attempts++;
        } while (User::where('referral_code', $code)->exists() && $attempts < 10);
        
        if ($attempts >= 10) {
            // Fallback to random string
            $code = 'REF' . strtoupper(Str::random(6));
        }
        
        $user->update(['referral_code' => $code]);
        
        return $code;
    }
    
    public function processReferral(User $newUser, string $referralCode): ?User
    {
        $referrer = User::where('referral_code', $referralCode)->first();
        
        if (!$referrer || $referrer->id === $newUser->id) {
            return null;
        }
        
        // Set referral relationship
        $newUser->update(['referred_by_id' => $referrer->id]);
        
        // Award signup points to referrer
        $this->loyaltyService->awardReferralPoints($referrer, $newUser, 'signup');
        
        // Give welcome bonus to new user
        $this->loyaltyService->addPoints($newUser, 100, 'Welcome bonus from referral');
        
        Log::info('Referral processed', [
            'referrer_id' => $referrer->id,
            'new_user_id' => $newUser->id,
            'referral_code' => $referralCode
        ]);
        
        return $referrer;
    }
    
    public function processFirstOrderReferral(User $user): void
    {
        if (!$user->referred_by_id) {
            return;
        }
        
        // Check if this is their first completed order
        $completedOrders = $user->orders()
            ->whereIn('status', ['PAID', 'DELIVERED'])
            ->count();
            
        if ($completedOrders !== 1) {
            return;
        }
        
        $referrer = $user->referrer;
        if ($referrer) {
            $this->loyaltyService->awardReferralPoints($referrer, $user, 'first_order');
            
            Log::info('First order referral bonus awarded', [
                'referrer_id' => $referrer->id,
                'referred_user_id' => $user->id
            ]);
        }
    }
    
    public function getReferralStats(User $user): array
    {
        $referred = $user->referredUsers()->count();
        $completedReferrals = $user->referredUsers()
            ->whereHas('orders', function ($query) {
                $query->whereIn('status', ['PAID', 'DELIVERED']);
            })->count();
            
        $totalEarned = $this->calculateReferralEarnings($user);
        
        return [
            'total_referred' => $referred,
            'completed_referrals' => $completedReferrals,
            'pending_referrals' => $referred - $completedReferrals,
            'total_earned' => $totalEarned,
            'referral_code' => $user->referral_code ?: $this->generateReferralCode($user),
            'referral_link' => $this->getReferralLink($user)
        ];
    }
    
    public function getReferralLink(User $user): string
    {
        $code = $user->referral_code ?: $this->generateReferralCode($user);
        return url('/?ref=' . $code);
    }
    
    public function getReferralLeaderboard(int $limit = 10): array
    {
        return User::select('users.*')
            ->selectRaw('COUNT(referred_users.id) as referral_count')
            ->selectRaw('SUM(CASE WHEN orders.status IN ("PAID", "DELIVERED") THEN orders.total ELSE 0 END) as total_referred_value')
            ->leftJoin('users as referred_users', 'users.id', '=', 'referred_users.referred_by_id')
            ->leftJoin('orders', 'referred_users.id', '=', 'orders.user_id')
            ->groupBy('users.id')
            ->having('referral_count', '>', 0)
            ->orderByDesc('referral_count')
            ->orderByDesc('total_referred_value')
            ->limit($limit)
            ->get()
            ->map(function ($user) {
                return [
                    'user' => $user,
                    'referral_count' => $user->referral_count,
                    'total_value' => $user->total_referred_value,
                    'estimated_earnings' => $user->total_referred_value * 0.05 // 5% commission example
                ];
            });
    }
    
    private function createReferralCode(string $name): string
    {
        // Create code based on name + random
        $cleanName = preg_replace('/[^a-zA-Z]/', '', $name);
        $nameCode = strtoupper(substr($cleanName, 0, 4));
        $random = strtoupper(Str::random(4));
        
        return $nameCode . $random;
    }
    
    private function calculateReferralEarnings(User $user): int
    {
        // This would typically query a referral_earnings table
        // For now, estimate based on referred user spending
        return $user->referredUsers()
            ->withSum(['orders' => function ($query) {
                $query->whereIn('status', ['PAID', 'DELIVERED']);
            }], 'total')
            ->get()
            ->sum('orders_sum_total') * 0.05; // 5% commission
    }
}