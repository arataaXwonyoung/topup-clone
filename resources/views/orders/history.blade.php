@extends('layouts.app')

@section('title', 'Riwayat Transaksi')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-yellow-400 mb-2">Riwayat Transaksi</h1>
        <p class="text-gray-400">Lihat semua transaksi top-up game Anda</p>
    </div>

    <!-- Filters -->
    <div class="glass rounded-xl p-6 mb-6">
        <form method="GET" action="{{ route('orders.history') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Cari Invoice/Game</label>
                <div class="relative">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Invoice atau nama game..."
                           class="w-full px-4 py-2 pl-10 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none">
                    <i data-lucide="search" class="absolute left-3 top-2.5 w-5 h-5 text-gray-400"></i>
                </div>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Status</label>
                <select name="status" 
                        class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="PENDING" {{ request('status') == 'PENDING' ? 'selected' : '' }}>Pending</option>
                    <option value="UNPAID" {{ request('status') == 'UNPAID' ? 'selected' : '' }}>Belum Bayar</option>
                    <option value="PAID" {{ request('status') == 'PAID' ? 'selected' : '' }}>Sudah Bayar</option>
                    <option value="DELIVERED" {{ request('status') == 'DELIVERED' ? 'selected' : '' }}>Selesai</option>
                    <option value="EXPIRED" {{ request('status') == 'EXPIRED' ? 'selected' : '' }}>Expired</option>
                    <option value="FAILED" {{ request('status') == 'FAILED' ? 'selected' : '' }}>Gagal</option>
                </select>
            </div>

            <!-- Date Range -->
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Dari Tanggal</label>
                <input type="date" 
                       name="from_date" 
                       value="{{ request('from_date') }}"
                       class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Sampai Tanggal</label>
                <input type="date" 
                       name="to_date" 
                       value="{{ request('to_date') }}"
                       class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none">
            </div>

            <!-- Submit Buttons -->
            <div class="md:col-span-4 flex gap-2">
                <button type="submit" 
                        class="px-6 py-2 bg-yellow-400 text-gray-900 rounded-lg font-semibold hover:bg-yellow-500 transition">
                    <i data-lucide="filter" class="inline w-4 h-4 mr-2"></i>
                    Filter
                </button>
                <a href="{{ route('orders.history') }}" 
                   class="px-6 py-2 border border-gray-600 text-gray-300 rounded-lg hover:bg-gray-800 transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="glass rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Total Transaksi</p>
                    <p class="text-2xl font-bold text-yellow-400">{{ $statistics['total'] ?? 0 }}</p>
                </div>
                <i data-lucide="shopping-cart" class="w-8 h-8 text-gray-600"></i>
            </div>
        </div>

        <div class="glass rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Berhasil</p>
                    <p class="text-2xl font-bold text-green-400">{{ $statistics['success'] ?? 0 }}</p>
                </div>
                <i data-lucide="check-circle" class="w-8 h-8 text-green-600"></i>
            </div>
        </div>

        <div class="glass rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Pending</p>
                    <p class="text-2xl font-bold text-yellow-400">{{ $statistics['pending'] ?? 0 }}</p>
                </div>
                <i data-lucide="clock" class="w-8 h-8 text-yellow-600"></i>
            </div>
        </div>

        <div class="glass rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Total Spent</p>
                    <p class="text-xl font-bold text-yellow-400">Rp {{ number_format($statistics['total_spent'] ?? 0, 0, ',', '.') }}</p>
                </div>
                <i data-lucide="wallet" class="w-8 h-8 text-gray-600"></i>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="glass rounded-xl overflow-hidden">
        @if($orders->count() > 0)
            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-800/50 border-b border-gray-700">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                Invoice
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                Game
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                Item
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                Total
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                Tanggal
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach($orders as $order)
                        <tr class="hover:bg-gray-800/30 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div>
                                        <div class="text-sm font-medium text-gray-100">
                                            {{ $order->invoice_no }}
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            ID: {{ $order->account_id }}
                                            @if($order->server_id)
                                                ({{ $order->server_id }})
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <img src="{{ $order->game->cover_path }}" 
                                         alt="{{ $order->game->name }}"
                                         class="w-10 h-10 rounded mr-3">
                                    <span class="text-sm text-gray-100">{{ $order->game->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-100">{{ $order->denomination->name }}</div>
                                <div class="text-xs text-gray-400">Qty: {{ $order->quantity }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-yellow-400">
                                    Rp {{ number_format($order->total, 0, ',', '.') }}
                                </div>
                                @if($order->discount > 0)
                                    <div class="text-xs text-green-400">
                                        -Rp {{ number_format($order->discount, 0, ',', '.') }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColors = [
                                        'PENDING' => 'bg-yellow-500',
                                        'UNPAID' => 'bg-orange-500',
                                        'PAID' => 'bg-blue-500',
                                        'DELIVERED' => 'bg-green-500',
                                        'EXPIRED' => 'bg-gray-500',
                                        'FAILED' => 'bg-red-500',
                                        'REFUNDED' => 'bg-purple-500',
                                    ];
                                    $statusLabels = [
                                        'PENDING' => 'Menunggu',
                                        'UNPAID' => 'Belum Bayar',
                                        'PAID' => 'Dibayar',
                                        'DELIVERED' => 'Selesai',
                                        'EXPIRED' => 'Kadaluarsa',
                                        'FAILED' => 'Gagal',
                                        'REFUNDED' => 'Refund',
                                    ];
                                @endphp
                                <span class="px-2 py-1 text-xs font-semibold rounded-full text-white {{ $statusColors[$order->status] ?? 'bg-gray-500' }}">
                                    {{ $statusLabels[$order->status] ?? $order->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-100">
                                    {{ $order->created_at->format('d M Y') }}
                                </div>
                                <div class="text-xs text-gray-400">
                                    {{ $order->created_at->format('H:i') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex space-x-2">
                                    <a href="{{ route('invoices.show', $order->invoice_no) }}" 
                                       class="text-yellow-400 hover:text-yellow-300 transition"
                                       title="Lihat Invoice">
                                        <i data-lucide="eye" class="w-5 h-5"></i>
                                    </a>
                                    @if($order->canBePaid())
                                        <a href="{{ route('invoices.show', $order->invoice_no) }}" 
                                           class="text-green-400 hover:text-green-300 transition"
                                           title="Bayar Sekarang">
                                            <i data-lucide="credit-card" class="w-5 h-5"></i>
                                        </a>
                                    @endif
                                    @if($order->status === 'DELIVERED' && !$order->review)
                                        <button onclick="openReviewModal({{ $order->id }})" 
                                                class="text-blue-400 hover:text-blue-300 transition"
                                                title="Beri Review">
                                            <i data-lucide="star" class="w-5 h-5"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="md:hidden space-y-4 p-4">
                @foreach($orders as $order)
                <div class="glass rounded-lg p-4">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <p class="font-mono text-sm text-yellow-400">{{ $order->invoice_no }}</p>
                            <p class="text-xs text-gray-400 mt-1">
                                {{ $order->created_at->format('d M Y H:i') }}
                            </p>
                        </div>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full text-white {{ $statusColors[$order->status] ?? 'bg-gray-500' }}">
                            {{ $statusLabels[$order->status] ?? $order->status }}
                        </span>
                    </div>

                    <div class="flex items-center mb-3">
                        <img src="{{ $order->game->cover_path }}" 
                             alt="{{ $order->game->name }}"
                             class="w-12 h-12 rounded mr-3">
                        <div>
                            <p class="font-semibold text-gray-100">{{ $order->game->name }}</p>
                            <p class="text-sm text-gray-400">{{ $order->denomination->name }}</p>
                        </div>
                    </div>

                    <div class="flex justify-between items-center pt-3 border-t border-gray-700">
                        <div>
                            <p class="text-sm text-gray-400">Total</p>
                            <p class="font-semibold text-yellow-400">
                                Rp {{ number_format($order->total, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="flex space-x-2">
                            <a href="{{ route('invoices.show', $order->invoice_no) }}" 
                               class="px-3 py-1 bg-gray-700 rounded text-sm hover:bg-gray-600 transition">
                                <i data-lucide="eye" class="inline w-4 h-4 mr-1"></i>
                                Lihat
                            </a>
                            @if($order->canBePaid())
                                <a href="{{ route('invoices.show', $order->invoice_no) }}" 
                                   class="px-3 py-1 bg-yellow-400 text-gray-900 rounded text-sm hover:bg-yellow-500 transition">
                                    <i data-lucide="credit-card" class="inline w-4 h-4 mr-1"></i>
                                    Bayar
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t border-gray-700">
                {{ $orders->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <div class="w-24 h-24 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="shopping-bag" class="w-12 h-12 text-gray-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-300 mb-2">Belum Ada Transaksi</h3>
                <p class="text-gray-400 mb-6">Anda belum melakukan transaksi apapun</p>
                <a href="{{ route('home') }}" 
                   class="inline-block px-6 py-3 bg-yellow-400 text-gray-900 rounded-lg font-semibold hover:bg-yellow-500 transition">
                    <i data-lucide="shopping-cart" class="inline w-5 h-5 mr-2"></i>
                    Mulai Top Up
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Review Modal -->
<div id="reviewModal" 
     x-data="{ open: false, orderId: null, rating: 0 }" 
     x-show="open" 
     @open-review.window="open = true; orderId = $event.detail.orderId"
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="display: none;">
    <div class="fixed inset-0 bg-black opacity-50" @click="open = false"></div>
    
    <div class="relative glass rounded-xl p-6 max-w-md w-full">
        <h3 class="text-xl font-semibold mb-4">Beri Review</h3>
        
        <form method="POST" :action="`/orders/${orderId}/review`">
            @csrf
            
            <!-- Rating Stars -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-300 mb-2">Rating</label>
                <div class="flex space-x-2">
                    @for($i = 1; $i <= 5; $i++)
                    <button type="button"
                            @click="rating = {{ $i }}"
                            class="text-3xl transition">
                        <i data-lucide="star" 
                           :class="rating >= {{ $i }} ? 'text-yellow-400 fill-current' : 'text-gray-600'"></i>
                    </button>
                    @endfor
                </div>
                <input type="hidden" name="rating" x-model="rating" required>
            </div>
            
            <!-- Comment -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Komentar (Opsional)</label>
                <textarea name="comment" 
                          rows="3"
                          class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none"
                          placeholder="Bagikan pengalaman Anda..."></textarea>
            </div>
            
            <!-- Buttons -->
            <div class="flex space-x-3">
                <button type="submit" 
                        class="flex-1 py-2 bg-yellow-400 text-gray-900 rounded-lg font-semibold hover:bg-yellow-500 transition">
                    Kirim Review
                </button>
                <button type="button" 
                        @click="open = false"
                        class="flex-1 py-2 border border-gray-600 rounded-lg hover:bg-gray-700 transition">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Initialize Lucide icons
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });

    function openReviewModal(orderId) {
        window.dispatchEvent(new CustomEvent('open-review', { 
            detail: { orderId: orderId } 
        }));
    }
</script>
@endpush