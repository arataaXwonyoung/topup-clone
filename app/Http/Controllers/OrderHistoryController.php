<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderHistoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display user's order history
     */
    public function index(Request $request)
    {
        $query = Order::where('user_id', Auth::id())
            ->with(['game', 'denomination', 'payment']);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by invoice or game name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                  ->orWhereHas('game', function ($gameQuery) use ($search) {
                      $gameQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Get statistics
        $statistics = $this->getUserStatistics();

        return view('orders.history', compact('orders', 'statistics'));
    }

    /**
     * Show single order detail
     */
    public function show($invoiceNo)
    {
        $order = Order::where('invoice_no', $invoiceNo)
            ->where('user_id', Auth::id())
            ->with(['game', 'denomination', 'payment', 'review'])
            ->firstOrFail();

        return view('orders.show', compact('order'));
    }

    /**
     * Reorder the same item
     */
    public function reorder($invoiceNo)
    {
        $order = Order::where('invoice_no', $invoiceNo)
            ->where('user_id', Auth::id())
            ->with(['game', 'denomination'])
            ->firstOrFail();

        // Redirect to game page with pre-filled data
        return redirect()->route('games.show', $order->game->slug)
            ->with('reorder_data', [
                'denomination_id' => $order->denomination_id,
                'account_id' => $order->account_id,
                'server_id' => $order->server_id,
            ]);
    }

    /**
     * Submit review for completed order
     */
    public function submitReview(Request $request, $invoiceNo)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        $order = Order::where('invoice_no', $invoiceNo)
            ->where('user_id', Auth::id())
            ->where('status', 'DELIVERED')
            ->firstOrFail();

        // Check if review already exists
        if ($order->review) {
            return back()->with('error', 'Anda sudah memberikan review untuk pesanan ini.');
        }

        Review::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'game_id' => $order->game_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_verified' => true, // Auto verified for logged in users
            'is_approved' => false, // Needs admin approval
        ]);

        return back()->with('success', 'Terima kasih atas review Anda! Review akan ditampilkan setelah disetujui.');
    }

    /**
     * Cancel pending order
     */
    public function cancel($invoiceNo)
    {
        $order = Order::where('invoice_no', $invoiceNo)
            ->where('user_id', Auth::id())
            ->whereIn('status', ['PENDING', 'UNPAID'])
            ->firstOrFail();

        $order->update(['status' => 'FAILED']);
        
        if ($order->payment) {
            $order->payment->update(['status' => 'FAILED']);
        }

        return back()->with('success', 'Pesanan berhasil dibatalkan.');
    }

    /**
     * Download invoice PDF
     */
    public function downloadInvoice($invoiceNo)
    {
        $order = Order::where('invoice_no', $invoiceNo)
            ->where('user_id', Auth::id())
            ->with(['game', 'denomination', 'payment'])
            ->firstOrFail();

        $pdf = \PDF::loadView('invoices.pdf', compact('order'));
        
        return $pdf->download('invoice-' . $order->invoice_no . '.pdf');
    }

    /**
     * Get user statistics
     */
    private function getUserStatistics()
    {
        $userId = Auth::id();
        
        return [
            'total_orders' => Order::where('user_id', $userId)->count(),
            'completed_orders' => Order::where('user_id', $userId)
                ->whereIn('status', ['PAID', 'DELIVERED'])
                ->count(),
            'total_spent' => Order::where('user_id', $userId)
                ->whereIn('status', ['PAID', 'DELIVERED'])
                ->sum('total'),
            'pending_orders' => Order::where('user_id', $userId)
                ->whereIn('status', ['PENDING', 'UNPAID'])
                ->count(),
            'favorite_game' => Order::where('user_id', $userId)
                ->whereIn('status', ['PAID', 'DELIVERED'])
                ->with('game')
                ->get()
                ->groupBy('game_id')
                ->sortByDesc(function ($group) {
                    return $group->count();
                })
                ->first()?->first()?->game?->name ?? '-',
        ];
    }
}