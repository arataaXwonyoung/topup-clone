<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SupportController extends Controller
{
    public function index(Request $request)
    {
        $tickets = collect(); // Empty collection for now
        
        $stats = [
            'total_tickets' => 0,
            'open_tickets' => 0,
            'resolved_tickets' => 0,
            'average_response_time' => 'N/A',
        ];

        return view('user.support.index', compact('tickets', 'stats'));
    }

    public function create()
    {
        $categories = [
            'payment' => 'Masalah Pembayaran',
            'delivery' => 'Masalah Pengiriman',
            'account' => 'Masalah Akun',
            'refund' => 'Permintaan Refund',
            'technical' => 'Masalah Teknis',
            'other' => 'Lainnya'
        ];

        $recentOrders = \App\Models\Order::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('user.support.create', compact('categories', 'recentOrders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|in:payment,delivery,account,refund,technical,other',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        // For now, just redirect back with success message
        return redirect()->route('user.support')
            ->with('success', 'Tiket support berhasil dibuat. Tim kami akan segera merespon.');
    }

    public function show($ticketNumber)
    {
        // Simplified version for now
        return redirect()->route('user.support')
            ->with('info', 'Fitur detail tiket akan segera tersedia.');
    }
}