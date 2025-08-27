@extends('layouts.app')

@section('title', 'My Orders')

@section('content')
<div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-8 py-4 sm:py-8">
    <div class="glass rounded-xl p-4 sm:p-6 mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-yellow-400 mb-4">My Orders</h1>
        
        <!-- Filters -->
        <div class="space-y-4">
            <form method="GET" action="{{ route('user.orders') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <input type="text" 
                       name="search" 
                       placeholder="Search invoice or game..."
                       value="{{ request('search') }}"
                       class="px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none text-sm">
                
                <select name="status" 
                        class="px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none text-sm">
                    <option value="">All Status</option>
                    <option value="PENDING" {{ request('status') == 'PENDING' ? 'selected' : '' }}>Pending</option>
                    <option value="PAID" {{ request('status') == 'PAID' ? 'selected' : '' }}>Paid</option>
                    <option value="DELIVERED" {{ request('status') == 'DELIVERED' ? 'selected' : '' }}>Delivered</option>
                    <option value="EXPIRED" {{ request('status') == 'EXPIRED' ? 'selected' : '' }}>Expired</option>
                    <option value="FAILED" {{ request('status') == 'FAILED' ? 'selected' : '' }}>Failed</option>
                </select>
                
                <input type="date" 
                       name="date_from" 
                       value="{{ request('date_from') }}"
                       class="px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none text-sm">
                
                <input type="date" 
                       name="date_to" 
                       value="{{ request('date_to') }}"
                       class="px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none text-sm">
                
                <div class="flex space-x-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-yellow-400 text-gray-900 rounded-lg hover:bg-yellow-500 transition text-sm font-medium">
                        <i data-lucide="search" class="inline w-4 h-4 mr-1"></i>
                        Filter
                    </button>
                    <a href="{{ route('user.orders') }}" class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition text-sm">
                        <i data-lucide="x" class="inline w-4 h-4"></i>
                    </a>
                </div>
            </form>
            
            <!-- Quick Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="bg-gray-800/50 rounded-lg p-3 text-center">
                    <div class="text-2xl font-bold text-white">{{ $stats['total'] ?? $orders->total() }}</div>
                    <div class="text-xs text-gray-400">Total Orders</div>
                </div>
                <div class="bg-green-500/10 rounded-lg p-3 text-center border border-green-500/20">
                    <div class="text-2xl font-bold text-green-400">{{ $stats['delivered'] ?? 0 }}</div>
                    <div class="text-xs text-gray-400">Delivered</div>
                </div>
                <div class="bg-yellow-500/10 rounded-lg p-3 text-center border border-yellow-500/20">
                    <div class="text-2xl font-bold text-yellow-400">{{ $stats['pending'] ?? 0 }}</div>
                    <div class="text-xs text-gray-400">Pending</div>
                </div>
                <div class="bg-blue-500/10 rounded-lg p-3 text-center border border-blue-500/20">
                    <div class="text-2xl font-bold text-blue-400">Rp {{ number_format($stats['total_spent'] ?? 0, 0, ',', '.') }}</div>
                    <div class="text-xs text-gray-400">Total Spent</div>
                </div>
            </div>
        </div>
    </div>

    @if($orders && $orders->count() > 0)
        <div class="space-y-4 sm:space-y-6">
            @foreach($orders as $order)
            <div class="glass rounded-xl p-4 sm:p-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                    <div class="flex items-start space-x-3 sm:space-x-4">
                        <div class="w-12 h-12 sm:w-16 sm:h-16 bg-gray-700 rounded-lg flex items-center justify-center flex-shrink-0">
                            @if($order->game && $order->game->image)
                                <img src="{{ asset('images/games/' . $order->game->image) }}" 
                                     alt="{{ $order->game->name }}"
                                     class="w-full h-full object-cover rounded-lg">
                            @else
                                <i data-lucide="gamepad-2" class="w-6 h-6 sm:w-8 sm:h-8 text-gray-400"></i>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                <span class="font-mono text-xs sm:text-sm text-gray-400">#{{ $order->invoice_no ?? 'N/A' }}</span>
                                <span class="px-2 py-1 rounded text-xs font-semibold
                                    {{ $order->status == 'DELIVERED' ? 'bg-green-500' : '' }}
                                    {{ $order->status == 'PAID' ? 'bg-blue-500' : '' }}
                                    {{ in_array($order->status, ['PENDING', 'UNPAID']) ? 'bg-yellow-500' : '' }}
                                    {{ in_array($order->status, ['EXPIRED', 'FAILED']) ? 'bg-red-500' : '' }}
                                    text-white">
                                    {{ $order->status ?? 'UNKNOWN' }}
                                </span>
                            </div>
                            <h3 class="font-semibold text-base sm:text-lg text-white mb-1">{{ $order->game->name ?? 'Unknown Game' }}</h3>
                            <p class="text-gray-400 text-sm sm:text-base mb-2">{{ $order->denomination->name ?? 'Unknown Item' }}</p>
                            <div class="flex flex-wrap items-center gap-2 text-xs sm:text-sm text-gray-500">
                                <span>{{ $order->created_at ? $order->created_at->format('d M Y H:i') : 'Unknown date' }}</span>
                                @if($order->payment && $order->payment->method)
                                    <span>•</span>
                                    <span>{{ $order->payment->method }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row sm:items-center space-y-3 sm:space-y-0 sm:space-x-4">
                        <div class="text-center sm:text-right">
                            <p class="text-lg sm:text-xl font-bold text-yellow-400">
                                Rp {{ number_format($order->total ?? 0, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('user.orders.show', $order->invoice_no) }}" 
                               class="px-3 py-2 bg-gray-700 rounded-lg hover:bg-gray-600 transition text-xs font-medium">
                                <i data-lucide="eye" class="inline w-3 h-3 mr-1"></i>
                                Details
                            </a>
                            
                            @if($order->status === 'PENDING' && $order->created_at->gt(now()->subMinutes(30)))
                            <button onclick="cancelOrder('{{ $order->invoice_no }}')" 
                                    class="px-3 py-2 bg-red-600 rounded-lg hover:bg-red-700 transition text-xs font-medium text-white">
                                <i data-lucide="x-circle" class="inline w-3 h-3 mr-1"></i>
                                Cancel
                            </button>
                            @endif
                            
                            @if($order->status === 'PENDING')
                            <a href="{{ route('invoices.show', $order->invoice_no) }}" 
                               class="px-3 py-2 bg-blue-600 rounded-lg hover:bg-blue-700 transition text-xs font-medium text-white">
                                <i data-lucide="credit-card" class="inline w-3 h-3 mr-1"></i>
                                Pay Now
                            </a>
                            @endif
                            
                            @if(in_array($order->status, ['PAID', 'DELIVERED']))
                            <a href="{{ route('user.orders.invoice', $order->invoice_no) }}" 
                               class="px-3 py-2 bg-yellow-400 text-gray-900 rounded-lg hover:bg-yellow-500 transition text-xs font-medium">
                                <i data-lucide="download" class="inline w-3 h-3 mr-1"></i>
                                Invoice
                            </a>
                            @endif
                            
                            @if($order->status === 'DELIVERED' && !$order->reviews->where('user_id', auth()->id())->count())
                            <a href="{{ route('user.reviews.create', ['order' => $order->id]) }}" 
                               class="px-3 py-2 bg-purple-600 rounded-lg hover:bg-purple-700 transition text-xs font-medium text-white">
                                <i data-lucide="star" class="inline w-3 h-3 mr-1"></i>
                                Review
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-6 sm:mt-8">
            {{ $orders->appends(request()->query())->links() }}
        </div>
    @else
        <div class="glass rounded-xl p-6 sm:p-12 text-center">
            <i data-lucide="inbox" class="w-12 h-12 sm:w-16 sm:h-16 mx-auto mb-4 text-gray-500"></i>
            <h3 class="text-lg sm:text-xl font-semibold text-gray-200 mb-2">No Orders Found</h3>
            <p class="text-sm sm:text-base text-gray-400 mb-6">You haven't made any orders yet or no orders match your filters.</p>
            <a href="{{ route('home') }}" class="inline-flex items-center px-6 py-3 bg-yellow-400 text-gray-900 rounded-lg hover:bg-yellow-500 font-medium">
                <i data-lucide="shopping-cart" class="w-5 h-5 mr-2"></i>
                Start Shopping
            </a>
        </div>
    @endif
</div>

@push('scripts')
<script>
// Cancel Order Function
async function cancelOrder(invoiceNo) {
    if (!confirm('Apakah kamu yakin ingin membatalkan order ini? Tindakan ini tidak dapat dibatalkan.')) {
        return;
    }
    
    const loadingToast = showToast('Membatalkan order...', 'info', 0);
    
    try {
        const response = await fetch(`/user/orders/${invoiceNo}/cancel`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        
        // Remove loading toast
        if (loadingToast) document.body.removeChild(loadingToast);
        
        if (data.success) {
            showToast('Order berhasil dibatalkan', 'success');
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showToast(data.message || 'Gagal membatalkan order', 'error');
        }
    } catch (error) {
        // Remove loading toast
        if (loadingToast) document.body.removeChild(loadingToast);
        showToast('Terjadi kesalahan sistem', 'error');
        console.error('Cancel order error:', error);
    }
}

// Toast Notification System
function showToast(message, type = 'info', duration = 3000) {
    const toast = document.createElement('div');
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        info: 'bg-blue-500',
        warning: 'bg-yellow-500'
    };
    
    toast.className = `fixed top-4 right-4 ${colors[type] || colors.info} text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-all duration-300 transform translate-x-full`;
    toast.innerHTML = `
        <div class="flex items-center space-x-2">
            <i data-lucide="${type === 'success' ? 'check-circle' : type === 'error' ? 'x-circle' : 'info'}" class="w-5 h-5"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Trigger animation
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }, 10);
    
    // Auto remove
    if (duration > 0) {
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => {
                if (document.body.contains(toast)) {
                    document.body.removeChild(toast);
                }
            }, 300);
        }, duration);
    }
    
    return toast;
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Auto-submit form on status change
    const statusSelect = document.querySelector('select[name="status"]');
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            this.closest('form').submit();
        });
    }
    
    // Add smooth loading states
    const actionButtons = document.querySelectorAll('a[href*="orders"], a[href*="invoices"]');
    actionButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!this.classList.contains('no-loading')) {
                this.style.opacity = '0.7';
                this.innerHTML = `<i class="inline w-3 h-3 mr-1 animate-spin">⏳</i> Loading...`;
            }
        });
    });
    
    // Refresh page data every 30 seconds for pending orders
    const hasPendingOrders = document.querySelector('.bg-yellow-500');
    if (hasPendingOrders) {
        setInterval(() => {
            // Only refresh if user hasn't interacted recently
            const lastActivity = localStorage.getItem('lastActivity') || Date.now();
            if (Date.now() - lastActivity > 25000) { // 25 seconds of inactivity
                location.reload();
            }
        }, 30000);
        
        // Track user activity
        ['click', 'scroll', 'keypress'].forEach(event => {
            document.addEventListener(event, () => {
                localStorage.setItem('lastActivity', Date.now());
            });
        });
    }
    
    console.log('Enhanced orders page initialized');
});
</script>
@endpush
@endsection