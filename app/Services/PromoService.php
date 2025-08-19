<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Promo;
use App\Models\User;

class PromoService
{
    public function validatePromo(string $code, float $subtotal, int $gameId, ?User $user = null): array
    {
        $promo = Promo::where('code', strtoupper($code))->first();

        if (!$promo) {
            return [
                'valid' => false,
                'message' => 'Kode promo tidak ditemukan',
            ];
        }

        if (!$promo->isValid()) {
            return [
                'valid' => false,
                'message' => 'Kode promo sudah tidak berlaku',
            ];
        }

        if (!$promo->isValidForGame($gameId)) {
            return [
                'valid' => false,
                'message' => 'Kode promo tidak berlaku untuk game ini',
            ];
        }

        if ($subtotal < $promo->min_total) {
            return [
                'valid' => false,
                'message' => 'Minimal transaksi Rp ' . number_format($promo->min_total, 0, ',', '.'),
            ];
        }

        // Check user usage limit
        if ($user && $promo->per_user_limit > 0) {
            $userUsageCount = Order::where('user_id', $user->id)
                ->where('promo_code', $promo->code)
                ->whereIn('status', ['PAID', 'DELIVERED'])
                ->count();

            if ($userUsageCount >= $promo->per_user_limit) {
                return [
                    'valid' => false,
                    'message' => 'Anda sudah mencapai batas penggunaan promo ini',
                ];
            }
        }

        $discount = $promo->calculateDiscount($subtotal);

        return [
            'valid' => true,
            'promo' => $promo,
            'discount' => $discount,
            'message' => 'Promo berhasil diterapkan',
        ];
    }

    public function applyPromo(Order $order, string $code): bool
    {
        $validation = $this->validatePromo(
            $code,
            $order->subtotal,
            $order->game_id,
            $order->user
        );

        if (!$validation['valid']) {
            return false;
        }

        $order->update([
            'promo_code' => $code,
            'discount' => $validation['discount'],
            'total' => $order->subtotal - $validation['discount'] + $order->fee,
        ]);

        return true;
    }
}