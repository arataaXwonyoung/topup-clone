<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
        $this->middleware(['auth:sanctum', 'admin']);
    }

    /**
     * List orders with filters
     * GET /admin/api/orders
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'nullable|array',
                'status.*' => 'in:PENDING,PAID,PROCESSING,DELIVERED,FAILED,EXPIRED,CANCELLED',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
                'game_id' => 'nullable|exists:games,id',
                'user_id' => 'nullable|exists:users,id',
                'payment_method' => 'nullable|string',
                'per_page' => 'nullable|integer|min:1|max:100',
                'page' => 'nullable|integer|min:1',
                'sort_by' => 'nullable|in:created_at,updated_at,total,status',
                'sort_order' => 'nullable|in:asc,desc',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = Order::with(['user', 'game', 'denomination', 'payments']);

            // Apply filters
            if ($request->has('status') && is_array($request->status)) {
                $query->whereIn('status', $request->status);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            if ($request->filled('game_id')) {
                $query->where('game_id', $request->game_id);
            }

            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->filled('payment_method')) {
                $query->where('payment_method', $request->payment_method);
            }

            // Search by invoice number, player ID, or customer name
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%")
                      ->orWhere('player_id', 'like', "%{$search}%")
                      ->orWhere('customer_name', 'like', "%{$search}%")
                      ->orWhere('customer_email', 'like', "%{$search}%");
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $orders = $query->paginate($perPage);

            // Transform data
            $orders->getCollection()->transform(function ($order) {
                return $this->transformOrder($order);
            });

            return response()->json([
                'success' => true,
                'data' => $orders->items(),
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                    'last_page' => $orders->lastPage(),
                    'from' => $orders->firstItem(),
                    'to' => $orders->lastItem(),
                ],
                'summary' => $this->getOrdersSummary($request)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve orders',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get single order details
     * GET /admin/api/orders/{id}
     */
    public function show(string $id): JsonResponse
    {
        try {
            $order = Order::with(['user', 'game', 'denomination', 'payments'])
                ->where('id', $id)
                ->orWhere('invoice_number', $id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->transformOrderDetailed($order)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve order',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Update order status
     * PATCH /admin/api/orders/{id}/status
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:PENDING,PAID,PROCESSING,DELIVERED,FAILED,EXPIRED,CANCELLED',
                'notes' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $order = Order::find($id);
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            $newStatus = $request->status;
            $oldStatus = $order->status;

            // Update order
            $updateData = ['status' => $newStatus];

            if ($request->filled('notes')) {
                $updateData['notes'] = $request->notes;
            }

            // Set timestamps based on status
            switch ($newStatus) {
                case 'PAID':
                    if (!$order->paid_at) {
                        $updateData['paid_at'] = now();
                    }
                    break;

                case 'DELIVERED':
                    if (!$order->delivered_at) {
                        $updateData['delivered_at'] = now();
                    }
                    break;
            }

            $order->update($updateData);

            // Log status change
            \Log::info('Order status updated by admin', [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'admin_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'data' => $this->transformOrder($order->fresh(['user', 'game', 'denomination', 'payments']))
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Process manual refund
     * POST /admin/api/refunds
     */
    public function processRefund(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'order_id' => 'required|exists:orders,id',
                'refund_amount' => 'required|numeric|min:0',
                'reason' => 'required|string|max:500',
                'refund_method' => 'required|in:original,manual,balance',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $order = Order::with('payments')->find($request->order_id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            // Check if order is eligible for refund
            if (!in_array($order->status, ['PAID', 'DELIVERED', 'PROCESSING'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order is not eligible for refund'
                ], 400);
            }

            $refundAmount = $request->refund_amount;

            // Validate refund amount
            if ($refundAmount > $order->total) {
                return response()->json([
                    'success' => false,
                    'message' => 'Refund amount cannot exceed order total'
                ], 400);
            }

            // Process refund based on method
            $refundResult = $this->processRefundByMethod(
                $order,
                $refundAmount,
                $request->refund_method,
                $request->reason
            );

            if (!$refundResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $refundResult['message']
                ], 400);
            }

            // Update order status
            $order->update([
                'status' => $refundAmount >= $order->total ? 'REFUNDED' : 'PARTIALLY_REFUNDED',
                'notes' => ($order->notes ?? '') . "\nRefund processed: " . $request->reason,
            ]);

            // Log refund
            \Log::info('Manual refund processed', [
                'order_id' => $order->id,
                'refund_amount' => $refundAmount,
                'method' => $request->refund_method,
                'admin_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Refund processed successfully',
                'data' => [
                    'refund_id' => $refundResult['refund_id'],
                    'refund_amount' => $refundAmount,
                    'order_status' => $order->status,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process refund',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get daily transaction report
     * GET /admin/api/reports/daily
     */
    public function dailyReport(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'date' => 'nullable|date',
                'timezone' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $date = $request->get('date', now()->format('Y-m-d'));
            $timezone = $request->get('timezone', 'Asia/Jakarta');

            // Set timezone for accurate reporting
            $reportDate = Carbon::parse($date)->setTimezone($timezone);
            $startOfDay = $reportDate->copy()->startOfDay();
            $endOfDay = $reportDate->copy()->endOfDay();

            // Convert back to UTC for database queries
            $startUTC = $startOfDay->utc();
            $endUTC = $endOfDay->utc();

            $report = [
                'date' => $reportDate->format('Y-m-d'),
                'timezone' => $timezone,
                'summary' => [
                    'total_orders' => Order::whereBetween('created_at', [$startUTC, $endUTC])->count(),
                    'total_revenue' => Order::whereBetween('created_at', [$startUTC, $endUTC])
                        ->whereIn('status', ['PAID', 'DELIVERED'])
                        ->sum('total'),
                    'successful_orders' => Order::whereBetween('created_at', [$startUTC, $endUTC])
                        ->whereIn('status', ['PAID', 'DELIVERED'])
                        ->count(),
                    'pending_orders' => Order::whereBetween('created_at', [$startUTC, $endUTC])
                        ->where('status', 'PENDING')
                        ->count(),
                    'failed_orders' => Order::whereBetween('created_at', [$startUTC, $endUTC])
                        ->whereIn('status', ['FAILED', 'EXPIRED', 'CANCELLED'])
                        ->count(),
                ],
                'hourly_breakdown' => $this->getHourlyBreakdown($startUTC, $endUTC),
                'top_games' => $this->getTopGamesByDay($startUTC, $endUTC),
                'payment_methods' => $this->getPaymentMethodsByDay($startUTC, $endUTC),
                'status_breakdown' => $this->getStatusBreakdown($startUTC, $endUTC),
            ];

            // Calculate metrics
            $report['summary']['conversion_rate'] = $report['summary']['total_orders'] > 0 
                ? round(($report['summary']['successful_orders'] / $report['summary']['total_orders']) * 100, 2)
                : 0;

            $report['summary']['average_order_value'] = $report['summary']['successful_orders'] > 0
                ? round($report['summary']['total_revenue'] / $report['summary']['successful_orders'], 0)
                : 0;

            return response()->json([
                'success' => true,
                'data' => $report
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate daily report',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Transform order data for API response
     */
    protected function transformOrder(Order $order): array
    {
        return [
            'id' => $order->id,
            'invoice_number' => $order->invoice_number,
            'status' => $order->status,
            'user' => [
                'id' => $order->user?->id,
                'name' => $order->user?->name ?? $order->customer_name,
                'email' => $order->user?->email ?? $order->customer_email,
            ],
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
            'payment_method' => $order->payment_method,
            'expires_at' => $order->expires_at,
            'paid_at' => $order->paid_at,
            'delivered_at' => $order->delivered_at,
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
        ];
    }

    /**
     * Transform order data with detailed information
     */
    protected function transformOrderDetailed(Order $order): array
    {
        $baseData = $this->transformOrder($order);

        $baseData['customer_details'] = [
            'name' => $order->customer_name,
            'email' => $order->customer_email,
            'phone' => $order->customer_phone,
        ];

        $baseData['promo_code'] = $order->promo_code;
        $baseData['notes'] = $order->notes;
        $baseData['metadata'] = $order->metadata;

        $baseData['payments'] = $order->payments->map(function ($payment) {
            return [
                'id' => $payment->id,
                'payment_id' => $payment->payment_id,
                'method' => $payment->method,
                'channel' => $payment->channel,
                'status' => $payment->status,
                'amount' => $payment->amount,
                'fee' => $payment->fee,
                'virtual_account' => $payment->virtual_account,
                'payment_code' => $payment->payment_code,
                'gateway_transaction_id' => $payment->gateway_transaction_id,
                'paid_at' => $payment->paid_at,
                'created_at' => $payment->created_at,
            ];
        });

        return $baseData;
    }

    /**
     * Get orders summary for current filters
     */
    protected function getOrdersSummary(Request $request): array
    {
        $query = Order::query();

        // Apply same filters as index method
        if ($request->has('status') && is_array($request->status)) {
            $query->whereIn('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return [
            'total_orders' => $query->count(),
            'total_revenue' => $query->whereIn('status', ['PAID', 'DELIVERED'])->sum('total'),
            'avg_order_value' => $query->whereIn('status', ['PAID', 'DELIVERED'])->avg('total') ?: 0,
        ];
    }

    /**
     * Process refund by method
     */
    protected function processRefundByMethod(Order $order, float $amount, string $method, string $reason): array
    {
        switch ($method) {
            case 'original':
                // Process refund through original payment method
                return $this->processOriginalRefund($order, $amount, $reason);
            
            case 'manual':
                // Record manual refund (external process)
                return $this->recordManualRefund($order, $amount, $reason);
            
            case 'balance':
                // Add to user account balance
                return $this->refundToBalance($order, $amount, $reason);
            
            default:
                return ['success' => false, 'message' => 'Invalid refund method'];
        }
    }

    protected function processOriginalRefund(Order $order, float $amount, string $reason): array
    {
        // This would integrate with payment gateway to process automatic refund
        // For now, just record the refund
        return [
            'success' => true,
            'refund_id' => 'REFUND-' . uniqid(),
            'message' => 'Refund processed through original payment method'
        ];
    }

    protected function recordManualRefund(Order $order, float $amount, string $reason): array
    {
        return [
            'success' => true,
            'refund_id' => 'MANUAL-' . uniqid(),
            'message' => 'Manual refund recorded'
        ];
    }

    protected function refundToBalance(Order $order, float $amount, string $reason): array
    {
        if ($order->user) {
            $order->user->increment('balance', $amount);
            return [
                'success' => true,
                'refund_id' => 'BALANCE-' . uniqid(),
                'message' => 'Refund added to user account balance'
            ];
        }

        return ['success' => false, 'message' => 'User not found for balance refund'];
    }

    protected function getHourlyBreakdown($startUTC, $endUTC): array
    {
        // Implement hourly breakdown logic
        return [];
    }

    protected function getTopGamesByDay($startUTC, $endUTC): array
    {
        return Order::with('game')
            ->whereBetween('created_at', [$startUTC, $endUTC])
            ->whereIn('status', ['PAID', 'DELIVERED'])
            ->selectRaw('game_id, COUNT(*) as orders, SUM(total) as revenue')
            ->groupBy('game_id')
            ->orderBy('revenue', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'game' => $item->game->name,
                    'orders' => $item->orders,
                    'revenue' => $item->revenue,
                ];
            })
            ->toArray();
    }

    protected function getPaymentMethodsByDay($startUTC, $endUTC): array
    {
        return Order::whereBetween('created_at', [$startUTC, $endUTC])
            ->whereIn('status', ['PAID', 'DELIVERED'])
            ->whereNotNull('payment_method')
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total) as revenue')
            ->groupBy('payment_method')
            ->orderBy('revenue', 'desc')
            ->get()
            ->toArray();
    }

    protected function getStatusBreakdown($startUTC, $endUTC): array
    {
        return Order::whereBetween('created_at', [$startUTC, $endUTC])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();
    }
}