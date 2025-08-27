<?php
// app/Http/Controllers/User/OrderController.php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::where('user_id', auth()->id())
            ->with(['game', 'denomination', 'payment', 'reviews']);
            
        // Apply filters
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('invoice_no', 'like', "%{$request->search}%")
                  ->orWhereHas('game', function ($gameQuery) use ($request) {
                      $gameQuery->where('name', 'like', "%{$request->search}%");
                  });
            });
        }
        
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $orders = $query->latest()->paginate(10);
        
        // Calculate stats
        $stats = [
            'total' => auth()->user()->orders()->count(),
            'delivered' => auth()->user()->orders()->where('status', 'DELIVERED')->count(),
            'pending' => auth()->user()->orders()->where('status', 'PENDING')->count(),
            'total_spent' => auth()->user()->orders()->whereIn('status', ['PAID', 'DELIVERED'])->sum('total')
        ];
        
        return view('user.orders.index', compact('orders', 'stats'));
    }
    
    public function show($invoiceNo)
    {
        $order = Order::where('invoice_no', $invoiceNo)
            ->where('user_id', auth()->id())
            ->with(['game', 'denomination', 'payment'])
            ->firstOrFail();
        
        return view('user.orders.show', compact('order'));
    }
    
    public function downloadInvoice($invoiceNo)
    {
        $order = Order::where('invoice_no', $invoiceNo)
            ->where('user_id', auth()->id())
            ->with(['game', 'denomination', 'payment'])
            ->firstOrFail();
        
        $pdf = Pdf::loadView('invoices.pdf', compact('order'));
        
        return $pdf->download('invoice-' . $order->invoice_no . '.pdf');
    }
    
    public function cancel($invoiceNo)
    {
        $order = Order::where('invoice_no', $invoiceNo)
            ->where('user_id', auth()->id())
            ->firstOrFail();
        
        // Can only cancel pending orders within 30 minutes
        if ($order->status !== 'PENDING') {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak dapat dibatalkan. Status: ' . $order->status
            ], 400);
        }
        
        if ($order->created_at->lt(now()->subMinutes(30))) {
            return response()->json([
                'success' => false,
                'message' => 'Order sudah tidak dapat dibatalkan (lebih dari 30 menit)'
            ], 400);
        }
        
        try {
            $order->update([
                'status' => 'CANCELLED',
                'notes' => 'Dibatalkan oleh user pada ' . now()->format('d M Y H:i:s')
            ]);
            
            // If there was a payment, mark it as cancelled
            if ($order->payment) {
                $order->payment->update(['status' => 'cancelled']);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dibatalkan'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Cancel order error: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }
}