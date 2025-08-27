<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Game;
use App\Models\Denomination;
use App\Models\Promo;
use App\Services\OrderService;
use App\Services\PromoService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    protected OrderService $orderService;
    protected PromoService $promoService;

    public function __construct(OrderService $orderService, PromoService $promoService)
    {
        $this->orderService = $orderService;
        $this->promoService = $promoService;
    }

    /**
     * Create new order
     * POST /api/orders
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'game_id' => 'required|exists:games,id',
                'denomination_id' => 'required|exists:denominations,id',
                'player_id' => 'required|string|max:100',
                'server_id' => 'nullable|string|max:50',
                'quantity' => 'integer|min:1|max:10',
                'customer_name' => 'nullable|string|max:100',
                'customer_email' => 'nullable|email|max:100',
                'customer_phone' => 'nullable|string|max:20',
                'promo_code' => 'nullable|string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();
            
            // Get game and denomination
            $game = Game::with('denominations')->find($data['game_id']);
            $denomination = $game->denominations()->find($data['denomination_id']);

            if (!$denomination || !$denomination->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected product is not available'
                ], 400);
            }

            // Validate denomination belongs to game
            if ($denomination->game_id != $game->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid product selection'
                ], 400);
            }

            // Risk check - basic validation
            $riskCheck = $this->performRiskCheck($request, $data);
            if (!$riskCheck['passed']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order blocked by security check',
                    'reason' => $riskCheck['reason']
                ], 403);
            }

            // Calculate pricing
            $quantity = $data['quantity'] ?? 1;
            $subtotal = $denomination->price * $quantity;
            $discountAmount = 0;
            $promoDetails = null;

            // Apply promo code if provided
            if (!empty($data['promo_code'])) {
                $promoResult = $this->promoService->validateAndCalculatePromo(
                    $data['promo_code'],
                    $subtotal,
                    $game->id,
                    Auth::id()
                );

                if ($promoResult['valid']) {
                    $discountAmount = $promoResult['discount_amount'];
                    $promoDetails = $promoResult['promo'];
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid promo code',
                        'reason' => $promoResult['reason']
                    ], 400);
                }
            }

            $total = $subtotal - $discountAmount;

            // Create order
            $orderData = [
                'user_id' => Auth::id(),
                'game_id' => $game->id,
                'denomination_id' => $denomination->id,
                'player_id' => $data['player_id'],
                'server_id' => $data['server_id'] ?? null,
                'quantity' => $quantity,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'total' => $total,
                'customer_name' => $data['customer_name'] ?? Auth::user()?->name,
                'customer_email' => $data['customer_email'] ?? Auth::user()?->email,
                'customer_phone' => $data['customer_phone'] ?? Auth::user()?->phone,
                'promo_code' => $data['promo_code'] ?? null,
                'status' => 'PENDING',
                'expires_at' => now()->addHours(24),
                'metadata' => [
                    'user_agent' => $request->userAgent(),
                    'ip_address' => $request->ip(),
                    'risk_score' => $riskCheck['score'] ?? 0,
                ]
            ];

            $order = $this->orderService->createOrder($orderData);

            // If promo was used, increment usage
            if ($promoDetails) {
                $promoDetails->increment('used_count');
            }

            Log::info('Order created via API', [
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'total' => $total,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => [
                    'order' => [
                        'id' => $order->id,
                        'invoice_number' => $order->invoice_number,
                        'status' => $order->status,
                        'game' => [
                            'id' => $game->id,
                            'name' => $game->name,
                        ],
                        'product' => [
                            'id' => $denomination->id,
                            'name' => $denomination->name,
                            'amount' => $denomination->amount,
                        ],
                        'player_id' => $order->player_id,
                        'server_id' => $order->server_id,
                        'quantity' => $order->quantity,
                        'subtotal' => $order->subtotal,
                        'discount_amount' => $order->discount_amount,
                        'total' => $order->total,
                        'expires_at' => $order->expires_at,
                        'created_at' => $order->created_at,
                    ],
                    'promo_applied' => $promoDetails ? [
                        'code' => $promoDetails->code,
                        'discount_amount' => $discountAmount,
                    ] : null,
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Order creation failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get order status
     * GET /api/orders/{id}
     */
    public function show(string $id): JsonResponse
    {
        try {
            $order = Order::with(['game', 'denomination', 'payments'])
                ->where('id', $id)
                ->orWhere('invoice_number', $id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            // Check if user can access this order
            if (Auth::check() && $order->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            // Get latest payment
            $latestPayment = $order->payments()->latest()->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'order' => [
                        'id' => $order->id,
                        'invoice_number' => $order->invoice_number,
                        'status' => $order->status,
                        'game' => [
                            'id' => $order->game->id,
                            'name' => $order->game->name,
                        ],
                        'product' => [
                            'id' => $order->denomination->id,
                            'name' => $order->denomination->name,
                            'amount' => $order->denomination->amount,
                        ],
                        'player_id' => $order->player_id,
                        'server_id' => $order->server_id,
                        'quantity' => $order->quantity,
                        'subtotal' => $order->subtotal,
                        'discount_amount' => $order->discount_amount,
                        'total' => $order->total,
                        'customer_name' => $order->customer_name,
                        'customer_email' => $order->customer_email,
                        'customer_phone' => $order->customer_phone,
                        'promo_code' => $order->promo_code,
                        'expires_at' => $order->expires_at,
                        'paid_at' => $order->paid_at,
                        'delivered_at' => $order->delivered_at,
                        'created_at' => $order->created_at,
                        'updated_at' => $order->updated_at,
                    ],
                    'payment' => $latestPayment ? [
                        'id' => $latestPayment->id,
                        'payment_id' => $latestPayment->payment_id,
                        'method' => $latestPayment->method,
                        'channel' => $latestPayment->channel,
                        'status' => $latestPayment->status,
                        'amount' => $latestPayment->amount,
                        'virtual_account' => $latestPayment->virtual_account,
                        'payment_code' => $latestPayment->payment_code,
                        'expired_at' => $latestPayment->expired_at,
                        'paid_at' => $latestPayment->paid_at,
                    ] : null,
                    'status_history' => $this->getOrderStatusHistory($order),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Order retrieval failed', [
                'order_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve order',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Perform basic risk assessment
     */
    protected function performRiskCheck(Request $request, array $data): array
    {
        $score = 0;
        $reasons = [];

        // Check user order frequency
        if (Auth::check()) {
            $recentOrders = Order::where('user_id', Auth::id())
                ->where('created_at', '>=', now()->subHours(1))
                ->count();

            if ($recentOrders >= 5) {
                $score += 30;
                $reasons[] = 'High order frequency';
            }

            // Check user limits
            $user = Auth::user();
            if ($user->max_orders_per_day) {
                $todayOrders = Order::where('user_id', Auth::id())
                    ->whereDate('created_at', today())
                    ->count();

                if ($todayOrders >= $user->max_orders_per_day) {
                    return [
                        'passed' => false,
                        'score' => 100,
                        'reason' => 'Daily order limit exceeded'
                    ];
                }
            }

            if ($user->daily_limit) {
                $todaySpend = Order::where('user_id', Auth::id())
                    ->whereDate('created_at', today())
                    ->whereIn('status', ['PAID', 'DELIVERED'])
                    ->sum('total');

                $denomination = Denomination::find($data['denomination_id']);
                $orderTotal = $denomination->price * ($data['quantity'] ?? 1);

                if (($todaySpend + $orderTotal) > $user->daily_limit) {
                    return [
                        'passed' => false,
                        'score' => 100,
                        'reason' => 'Daily spending limit exceeded'
                    ];
                }
            }
        }

        // Check IP-based orders
        $ipOrders = Order::where('created_at', '>=', now()->subHours(1))
            ->whereJsonContains('metadata->ip_address', $request->ip())
            ->count();

        if ($ipOrders >= 10) {
            $score += 40;
            $reasons[] = 'High IP order frequency';
        }

        // Check suspicious patterns
        if (strlen($data['player_id']) < 3) {
            $score += 20;
            $reasons[] = 'Suspicious player ID';
        }

        return [
            'passed' => $score < 70,
            'score' => $score,
            'reasons' => $reasons,
            'reason' => implode(', ', $reasons)
        ];
    }

    /**
     * Get order status history
     */
    protected function getOrderStatusHistory(Order $order): array
    {
        $history = [];

        $history[] = [
            'status' => 'PENDING',
            'timestamp' => $order->created_at,
            'description' => 'Order created and waiting for payment'
        ];

        if ($order->paid_at) {
            $history[] = [
                'status' => 'PAID',
                'timestamp' => $order->paid_at,
                'description' => 'Payment received successfully'
            ];
        }

        if ($order->delivered_at) {
            $history[] = [
                'status' => 'DELIVERED',
                'timestamp' => $order->delivered_at,
                'description' => 'Order delivered to customer'
            ];
        }

        if (in_array($order->status, ['FAILED', 'EXPIRED', 'CANCELLED'])) {
            $history[] = [
                'status' => $order->status,
                'timestamp' => $order->updated_at,
                'description' => match($order->status) {
                    'FAILED' => 'Order failed to process',
                    'EXPIRED' => 'Order expired due to timeout',
                    'CANCELLED' => 'Order cancelled',
                    default => 'Order status updated'
                }
            ];
        }

        return $history;
    }
}