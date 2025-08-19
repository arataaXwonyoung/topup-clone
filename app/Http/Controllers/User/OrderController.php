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
        $orders = Order::where('user_id', auth()->id())
            ->with(['game', 'denomination', 'payment'])
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->search, function ($query, $search) {
                return $query->where('invoice_no', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);
        
        return view('user.orders.index', compact('orders'));
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
}