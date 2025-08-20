<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PromoController extends Controller
{
    /**
     * Display list of available promos
     */
    public function index(Request $request)
    {
        // Get active promos
        $promos = Promo::active()
            ->when($request->type, function ($query, $type) {
                return $query->where('type', $type);
            })
            ->orderBy('value', 'desc')
            ->paginate(12);

        // Get user's promo usage stats
        $userPromoUsage = $this->getUserPromoUsage();

        // Get upcoming promos
        $upcomingPromos = Promo::where('is_active', true)
            ->where('starts_at', '>', now())
            ->orderBy('starts_at')
            ->limit(5)
            ->get();

        // Get expiring soon promos (within 3 days)
        $expiringPromos = Promo::active()
            ->whereBetween('ends_at', [now(), now()->addDays(3)])
            ->orderBy('ends_at')
            ->get();

        return view('user.promos.index', compact(
            'promos',
            'userPromoUsage',
            'upcomingPromos',
            'expiringPromos'
        ));
    }

    /**
     * Show single promo details
     */
    public function show($code)
    {
        $promo = Promo::where('code', $code)->firstOrFail();
        
        // Check if user has used this promo
        $userUsageCount = Order::where('user_id', Auth::id())
            ->where('promo_code', $code)
            ->whereIn('status', ['PAID', 'DELIVERED'])
            ->count();

        $canUse = $this->canUsePromo($promo, $userUsageCount);
        
        // Get eligible games if promo has game restrictions
        $eligibleGames = null;
        if ($promo->game_ids) {
            $eligibleGames = \App\Models\Game::whereIn('id', $promo->game_ids)
                ->where('is_active', true)
                ->get();
        }

        // Get similar promos
        $similarPromos = Promo::active()
            ->where('id', '!=', $promo->id)
            ->where('type', $promo->type)
            ->limit(4)
            ->get();

        return view('user.promos.show', compact(
            'promo',
            'userUsageCount',
            'canUse',
            'eligibleGames',
            'similarPromos'
        ));
    }

    /**
     * Check promo availability for user
     */
    public function check(Request $request)
    {
        $request->validate([
            'code' => 'required|string|exists:promos,code',
            'amount' => 'required|numeric|min:0'
        ]);

        $promo = Promo::where('code', $request->code)->first();
        
        if (!$promo || !$promo->isValid()) {
            return response()->json([
                'valid' => false,
                'message' => 'Kode promo tidak valid atau sudah kadaluarsa'
            ]);
        }

        // Check minimum amount
        if ($request->amount < $promo->min_total) {
            return response()->json([
                'valid' => false,
                'message' => 'Minimum transaksi Rp ' . number_format($promo->min_total, 0, ',', '.')
            ]);
        }

        // Check user usage limit
        $userUsageCount = Order::where('user_id', Auth::id())
            ->where('promo_code', $request->code)
            ->whereIn('status', ['PAID', 'DELIVERED'])
            ->count();

        if ($promo->per_user_limit && $userUsageCount >= $promo->per_user_limit) {
            return response()->json([
                'valid' => false,
                'message' => 'Anda sudah mencapai batas penggunaan promo ini'
            ]);
        }

        // Calculate discount
        $discount = $promo->calculateDiscount($request->amount);

        return response()->json([
            'valid' => true,
            'discount' => $discount,
            'final_amount' => $request->amount - $discount,
            'message' => 'Promo berhasil diterapkan! Potongan Rp ' . number_format($discount, 0, ',', '.')
        ]);
    }

    /**
     * Copy promo code to clipboard (returns formatted response)
     */
    public function copy($code)
    {
        $promo = Promo::where('code', $code)->firstOrFail();

        return response()->json([
            'success' => true,
            'code' => $promo->code,
            'message' => 'Kode promo berhasil disalin!'
        ]);
    }

    /**
     * Get user's promo history
     */
    public function history()
    {
        $usedPromos = Order::where('user_id', Auth::id())
            ->whereNotNull('promo_code')
            ->whereIn('status', ['PAID', 'DELIVERED'])
            ->select('promo_code', 'discount', 'created_at', 'total')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $totalSaved = Order::where('user_id', Auth::id())
            ->whereNotNull('promo_code')
            ->whereIn('status', ['PAID', 'DELIVERED'])
            ->sum('discount');

        return view('user.promos.history', compact('usedPromos', 'totalSaved'));
    }

    /**
     * Subscribe to promo notifications
     */
    public function subscribe(Request $request)
    {
        $user = Auth::user();
        $preferences = $user->preferences ?? [];
        $preferences['promo_notifications'] = true;
        $preferences['promo_categories'] = $request->categories ?? ['all'];
        
        $user->update(['preferences' => $preferences]);

        return back()->with('success', 'Berhasil berlangganan notifikasi promo!');
    }

    /**
     * Unsubscribe from promo notifications
     */
    public function unsubscribe()
    {
        $user = Auth::user();
        $preferences = $user->preferences ?? [];
        $preferences['promo_notifications'] = false;
        
        $user->update(['preferences' => $preferences]);

        return back()->with('success', 'Berhasil berhenti berlangganan notifikasi promo.');
    }

    /**
     * Get user's promo usage statistics
     */
    private function getUserPromoUsage()
    {
        $userId = Auth::id();
        
        return [
            'total_used' => Order::where('user_id', $userId)
                ->whereNotNull('promo_code')
                ->whereIn('status', ['PAID', 'DELIVERED'])
                ->count(),
            
            'total_saved' => Order::where('user_id', $userId)
                ->whereNotNull('promo_code')
                ->whereIn('status', ['PAID', 'DELIVERED'])
                ->sum('discount'),
            
            'most_used' => Order::where('user_id', $userId)
                ->whereNotNull('promo_code')
                ->whereIn('status', ['PAID', 'DELIVERED'])
                ->groupBy('promo_code')
                ->selectRaw('promo_code, COUNT(*) as usage_count, SUM(discount) as total_discount')
                ->orderByDesc('usage_count')
                ->first(),
            
            'last_used' => Order::where('user_id', $userId)
                ->whereNotNull('promo_code')
                ->whereIn('status', ['PAID', 'DELIVERED'])
                ->orderBy('created_at', 'desc')
                ->first()
        ];
    }

    /**
     * Check if user can use promo
     */
    private function canUsePromo($promo, $userUsageCount)
    {
        if (!$promo->isValid()) {
            return false;
        }

        if ($promo->per_user_limit && $userUsageCount >= $promo->per_user_limit) {
            return false;
        }

        return true;
    }
}