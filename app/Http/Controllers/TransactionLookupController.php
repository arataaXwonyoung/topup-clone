<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class TransactionLookupController extends Controller
{
    public function index()
    {
        return view('pages.check-transaction');
    }

    public function search(Request $request)
    {
        $request->validate([
            'search' => 'required|string|min:3'
        ]);

        $search = $request->search;

        $orders = Order::where(function ($query) use ($search) {
                $query->where('invoice_no', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('whatsapp', 'like', "%{$search}%");
            })
            ->with(['game', 'denomination', 'payment'])
            ->latest()
            ->paginate(10);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $orders
            ]);
        }

        return view('pages.check-transaction', compact('orders'));
    }

    public function show($invoiceNo)
    {
        $order = Order::where('invoice_no', $invoiceNo)
            ->with(['game', 'denomination', 'payment', 'review'])
            ->firstOrFail();

        return view('invoices.show', compact('order'));
    }
}