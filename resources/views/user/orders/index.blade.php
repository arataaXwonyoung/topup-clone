@extends('layouts.app')

@section('title', 'Order History')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="glass rounded-xl p-6 mb-8">
        <h1 class="text-3xl font-bold text-yellow-400 mb-4">Riwayat Order</h1>
        
        <!-- Filters -->
        <div class="flex flex-wrap gap-4">
            <form method="GET" action="{{ route('user.orders') }}" class="flex gap-3 flex-1">
                <input type="text" 
                       name="search" 
                       placeholder="Cari invoice..."
                       value="{{ request('search') }}"
                       class="flex-1 px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none">
                
                <select name="status" onchange="this.form.submit()" 
                        class="px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="PENDING" {{ request('status') == 'PENDING' ? 'selected' : '' }}>Pending</option>
                    <option value="PAID" {{ request('status') == 'PAID' ? 'selected' : '' }}>Paid</option>
                    <option value="DELIVERED" {{ request('status') == 'DELIVERED' ? 'selected' : '' }}>Delivered</option>
                    <option value="EXPIRED" {{ request('status') == 'EXPIRED' ? 'selected' : '' }}>Expired</option>
                    <option value="FAILED" {{ request('status') == 'FAILED' ? 'selected' : '' }}>Failed</option>
                </select>
                
                <button type="submit" class="px-4 py-2 bg-yellow-400 text-gray-900 rounded-lg hover:bg-yellow-500 transition">
                    Search
                </button>
            </form>
        </div>
    </div>

    @if($orders->count() > 0)
        <div class="space-y-4">
            @foreach($orders as $order)
            <div class="glass rounded-xl p-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-4 mb-3">
                            <span class="font-mono text-sm text-gray-400">#{{ $order->invoice_no }}</span>
                            <span class="px-2 py-1 rounded text-xs font-semibold
                                {{ $order->status == 'DELIVERED' ? 'bg-green-500' : '' }}
                                {{ $order->status == 'PAID' ? 'bg-blue-500' : '' }}
                                {{ in_array($order->status, ['PENDING', 'UNPAID']) ? 'bg-yellow-500' : '' }}
                                {{ in_array($order->status, ['EXPIRED', 'FAILED']) ? 'bg-red-500' : '' }}
                                text-white">
                                {{ $order->status }}
                            </span>
                        </div>
                        <h3 class="font-semibold text-lg text-white">{{ $order->game->name }}</h3>
                        <p class="text-gray-400">{{ $order->denomination->name }}</p>
                        <div class="flex items-center space-x-4 mt-2 text-sm text-gray-500">
                            <span>{{ $order->created_at->format('d M Y H:i') }}</span>
                            <span>•</span>
                            <span>{{ $order->payment->method ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xl font-bold text-yellow-400 mb-3">
                            Rp {{ number_format($order->total, 0, ',', '.') }}
                        </p>
                        <div class="space-x-2">
                            <a href="{{ route('user.orders.show', $order->invoice_no) }}" 
                               class="inline-block px-4 py-2 bg-gray-700 rounded-lg hover:bg-gray-600 transition">
                                Detail
                            </a>
                            @if(in_array($order->status, ['PAID', 'DELIVERED']))
                            <a href="{{ route('user.orders.invoice', $order->invoice_no) }}" 
                               class="inline-block px-4 py-2 bg-yellow-400 text-gray-900 rounded-lg hover:bg-yellow-500 transition">
                                Download Invoice
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $orders->links() }}
        </div>
    @else
        <div class="glass rounded-xl p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-200">Tidak ada order</h3>
            <p class="mt-1 text-sm text-gray-400">Belum ada transaksi yang ditemukan.</p>
        </div>
    @endif
</div>
@endsection