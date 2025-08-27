@extends('layouts.app')

@section('title', 'Order Details - ' . $order->invoice_no)

@section('content')
<div class="max-w-4xl mx-auto px-2 sm:px-4 lg:px-8 py-4 sm:py-8">
    <!-- Header -->
    <div class="glass rounded-xl p-4 sm:p-6 mb-6 sm:mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-yellow-400 mb-2">Order Details</h1>
                <p class="text-gray-400 text-sm sm:text-base">Invoice #{{ $order->invoice_no ?? 'N/A' }}</p>
            </div>
            <div class="text-center sm:text-right">
                <span class="inline-block px-4 py-2 rounded-full text-sm font-semibold
                    {{ $order->status == 'DELIVERED' ? 'bg-green-500' : '' }}
                    {{ $order->status == 'PAID' ? 'bg-blue-500' : '' }}
                    {{ in_array($order->status, ['PENDING', 'UNPAID']) ? 'bg-yellow-500' : '' }}
                    {{ in_array($order->status, ['EXPIRED', 'FAILED']) ? 'bg-red-500' : '' }}
                    text-white">
                    {{ $order->status ?? 'UNKNOWN' }}
                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-8">
        <!-- Order Information -->
        <div class="lg:col-span-2">
            <div class="glass rounded-xl p-4 sm:p-6 mb-6">
                <h2 class="text-lg sm:text-xl font-semibold mb-4">Order Information</h2>
                
                <div class="flex items-start space-x-4 mb-6">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gray-700 rounded-lg flex items-center justify-center flex-shrink-0">
                        @if($order->game && $order->game->image)
                            <img src="{{ asset('images/games/' . $order->game->image) }}" 
                                 alt="{{ $order->game->name }}"
                                 class="w-full h-full object-cover rounded-lg">
                        @else
                            <i data-lucide="gamepad-2" class="w-8 h-8 sm:w-10 sm:h-10 text-gray-400"></i>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="font-semibold text-lg sm:text-xl text-white mb-2">{{ $order->game->name ?? 'Unknown Game' }}</h3>
                        <p class="text-gray-400 text-sm sm:text-base mb-1">{{ $order->denomination->name ?? 'Unknown Item' }}</p>
                        <p class="text-xs sm:text-sm text-gray-500">{{ $order->game->description ?? '' }}</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Order Date:</span>
                        <span class="text-white">{{ $order->created_at ? $order->created_at->format('d M Y H:i') : 'Unknown' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Invoice Number:</span>
                        <span class="font-mono text-white">{{ $order->invoice_no ?? 'N/A' }}</span>
                    </div>
                    @if($order->player_id)
                    <div class="flex justify-between">
                        <span class="text-gray-400">Player ID:</span>
                        <span class="text-white">{{ $order->player_id }}</span>
                    </div>
                    @endif
                    @if($order->server)
                    <div class="flex justify-between">
                        <span class="text-gray-400">Server:</span>
                        <span class="text-white">{{ $order->server }}</span>
                    </div>
                    @endif
                    @if($order->zone)
                    <div class="flex justify-between">
                        <span class="text-gray-400">Zone:</span>
                        <span class="text-white">{{ $order->zone }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Payment Information -->
            @if($order->payment)
            <div class="glass rounded-xl p-4 sm:p-6 mb-6">
                <h2 class="text-lg sm:text-xl font-semibold mb-4">Payment Information</h2>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Payment Method:</span>
                        <span class="text-white">{{ $order->payment->method ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Payment Reference:</span>
                        <span class="font-mono text-white text-sm">{{ $order->payment->reference ?? 'N/A' }}</span>
                    </div>
                    @if($order->payment->paid_at)
                    <div class="flex justify-between">
                        <span class="text-gray-400">Paid At:</span>
                        <span class="text-white">{{ $order->payment->paid_at->format('d M Y H:i') }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Order Timeline -->
            <div class="glass rounded-xl p-4 sm:p-6">
                <h2 class="text-lg sm:text-xl font-semibold mb-4">Order Timeline</h2>
                
                <div class="space-y-4">
                    <div class="flex items-start space-x-3">
                        <div class="w-3 h-3 bg-yellow-400 rounded-full mt-2"></div>
                        <div>
                            <p class="font-medium text-white">Order Created</p>
                            <p class="text-sm text-gray-400">{{ $order->created_at ? $order->created_at->format('d M Y H:i') : 'Unknown' }}</p>
                        </div>
                    </div>
                    
                    @if($order->payment && $order->payment->paid_at)
                    <div class="flex items-start space-x-3">
                        <div class="w-3 h-3 bg-blue-400 rounded-full mt-2"></div>
                        <div>
                            <p class="font-medium text-white">Payment Confirmed</p>
                            <p class="text-sm text-gray-400">{{ $order->payment->paid_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                    @endif
                    
                    @if($order->status == 'DELIVERED')
                    <div class="flex items-start space-x-3">
                        <div class="w-3 h-3 bg-green-400 rounded-full mt-2"></div>
                        <div>
                            <p class="font-medium text-white">Order Completed</p>
                            <p class="text-sm text-gray-400">{{ $order->updated_at ? $order->updated_at->format('d M Y H:i') : 'Unknown' }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="lg:col-span-1">
            <div class="glass rounded-xl p-4 sm:p-6 mb-6">
                <h2 class="text-lg sm:text-xl font-semibold mb-4">Order Summary</h2>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Subtotal:</span>
                        <span class="text-white">Rp {{ number_format($order->subtotal ?? $order->total ?? 0, 0, ',', '.') }}</span>
                    </div>
                    @if(($order->discount ?? 0) > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-400">Discount:</span>
                        <span class="text-green-400">-Rp {{ number_format($order->discount, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @if(($order->admin_fee ?? 0) > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-400">Admin Fee:</span>
                        <span class="text-white">Rp {{ number_format($order->admin_fee, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="border-t border-gray-700 pt-3">
                        <div class="flex justify-between text-lg font-bold">
                            <span>Total:</span>
                            <span class="text-yellow-400">Rp {{ number_format($order->total ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="glass rounded-xl p-4 sm:p-6">
                <h2 class="text-lg font-semibold mb-4">Actions</h2>
                
                <div class="space-y-3">
                    <a href="{{ route('user.orders') }}" 
                       class="w-full flex items-center justify-center px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition">
                        <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                        Back to Orders
                    </a>
                    
                    @if(in_array($order->status, ['PAID', 'DELIVERED']))
                    <a href="{{ route('user.orders.invoice', $order->invoice_no) }}" 
                       class="w-full flex items-center justify-center px-4 py-2 bg-yellow-400 text-gray-900 rounded-lg hover:bg-yellow-500 transition">
                        <i data-lucide="download" class="w-4 h-4 mr-2"></i>
                        Download Invoice
                    </a>
                    @endif
                    
                    @if(in_array($order->status, ['PENDING', 'UNPAID']) && $order->canBePaid())
                    <a href="{{ route('invoices.show', $order->invoice_no) }}" 
                       class="w-full flex items-center justify-center px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition">
                        <i data-lucide="credit-card" class="w-4 h-4 mr-2"></i>
                        Pay Now
                    </a>
                    @endif
                    
                    @if($order->status == 'DELIVERED' && $order->game)
                    <a href="{{ route('games.show', $order->game->slug) }}" 
                       class="w-full flex items-center justify-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                        <i data-lucide="repeat" class="w-4 h-4 mr-2"></i>
                        Order Again
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    console.log('Order details page initialized');
});
</script>
@endpush
@endsection