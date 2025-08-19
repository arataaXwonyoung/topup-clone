@extends('layouts.app')

@section('title', 'Cek Transaksi')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="glass rounded-xl p-6 mb-8">
        <h1 class="text-3xl font-bold text-yellow-400 mb-4">Cek Status Transaksi</h1>
        <p class="text-gray-400">Masukkan nomor invoice atau email untuk melihat status transaksi kamu.</p>
    </div>

    <div class="glass rounded-xl p-6">
        <form method="GET" action="{{ route('transactions.check') }}">
            <div class="flex space-x-3">
                <input type="text" 
                       name="invoice" 
                       placeholder="Nomor Invoice atau Email"
                       value="{{ request('invoice') }}"
                       class="flex-1 px-4 py-3 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none"
                       required>
                <button type="submit" 
                        class="px-6 py-3 bg-yellow-400 text-gray-900 rounded-lg font-semibold hover:bg-yellow-500 transition">
                    Cek Status
                </button>
            </div>
        </form>
    </div>

    @if($order)
    <div class="mt-8 glass rounded-xl p-6">
        <h2 class="text-xl font-semibold mb-4">Detail Transaksi</h2>
        
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-gray-400">Invoice:</span>
                <span class="font-mono">{{ $order->invoice_no }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-400">Tanggal:</span>
                <span>{{ $order->created_at->format('d M Y H:i') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-400">Game:</span>
                <span>{{ $order->game->name }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-400">Item:</span>
                <span>{{ $order->denomination->name }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-400">Total:</span>
                <span class="text-yellow-400 font-bold">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-400">Status:</span>
                <span class="px-3 py-1 rounded text-xs font-semibold
                    {{ $order->status == 'DELIVERED' ? 'bg-green-500' : '' }}
                    {{ $order->status == 'PAID' ? 'bg-blue-500' : '' }}
                    {{ in_array($order->status, ['PENDING', 'UNPAID']) ? 'bg-yellow-500' : '' }}
                    {{ in_array($order->status, ['EXPIRED', 'FAILED']) ? 'bg-red-500' : '' }}
                    text-white">
                    {{ $order->status }}
                </span>
            </div>
        </div>

        <div class="mt-6 flex space-x-3">
            <a href="{{ route('invoices.show', $order->invoice_no) }}" 
               class="flex-1 text-center py-2 bg-yellow-400 text-gray-900 rounded-lg font-semibold hover:bg-yellow-500 transition">
                Lihat Invoice
            </a>
            @if($order->canBePaid())
            <a href="{{ route('invoices.show', $order->invoice_no) }}" 
               class="flex-1 text-center py-2 bg-green-500 text-white rounded-lg font-semibold hover:bg-green-600 transition">
                Bayar Sekarang
            </a>
            @endif
        </div>
    </div>
    @elseif(request('invoice'))
    <div class="mt-8 glass rounded-xl p-6 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-200">Transaksi tidak ditemukan</h3>
        <p class="mt-1 text-sm text-gray-400">Pastikan nomor invoice atau email yang kamu masukkan benar.</p>
    </div>
    @endif
</div>
@endsection