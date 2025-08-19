<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\ApplyPromoRequest;
use App\Services\OrderService;
use App\Services\PromoService;
use App\Models\Denomination;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    protected OrderService $orderService;
    protected PromoService $promoService;

    public function __construct(OrderService $orderService, PromoService $promoService)
    {
        $this->orderService = $orderService;
        $this->promoService = $promoService;
    }

    public function process(CheckoutRequest $request)
    {
        try {
            $order = $this->orderService->createOrder($request->validated());
            
            return response()->json([
                'success' => true,
                'invoice_no' => $order->invoice_no,
                'redirect_url' => route('invoices.show', $order->invoice_no),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses pesanan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function validatePromo(ApplyPromoRequest $request)
    {
        $denomination = Denomination::findOrFail($request->denomination_id);
        $subtotal = $denomination->price * ($request->quantity ?? 1);

        $validation = $this->promoService->validatePromo(
            $request->promo_code,
            $subtotal,
            $denomination->game_id,
            auth()->user()
        );

        if ($validation['valid']) {
            return response()->json([
                'success' => true,
                'discount' => $validation['discount'],
                'message' => $validation['message'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $validation['message'],
        ], 422);
    }
}