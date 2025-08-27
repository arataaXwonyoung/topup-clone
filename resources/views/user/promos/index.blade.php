@extends('layouts.app')

@section('title', 'My Promos')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="glass rounded-xl p-6 mb-8">
        <h1 class="text-3xl font-bold text-yellow-400 mb-2">Promo & Vouchers</h1>
        <p class="text-gray-400">Exclusive deals and discounts for you</p>
    </div>

    <!-- Filter Tabs -->
    <div class="flex space-x-4 mb-6">
        <button class="px-4 py-2 bg-yellow-400 text-gray-900 rounded-lg font-semibold">All Promos</button>
        <button class="px-4 py-2 glass rounded-lg hover:bg-gray-700 transition">Active</button>
        <button class="px-4 py-2 glass rounded-lg hover:bg-gray-700 transition">Expiring Soon</button>
        <button class="px-4 py-2 glass rounded-lg hover:bg-gray-700 transition">Used</button>
    </div>

    <!-- Promo Grid -->
    @if($promos && $promos->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($promos as $promo)
        <div class="glass rounded-xl overflow-hidden hover:transform hover:scale-105 transition">
            <div class="bg-gradient-to-r from-yellow-400 to-orange-400 p-4">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-900 font-bold text-2xl">
                            @if($promo->type == 'percent')
                                {{ $promo->value }}% OFF
                            @else
                                Rp {{ number_format($promo->value, 0, ',', '.') }} OFF
                            @endif
                        </p>
                        <p class="text-gray-800 text-sm">Min. Rp {{ number_format($promo->min_total ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <span class="bg-white/20 px-2 py-1 rounded text-xs text-gray-900">
                        @if($promo->ends_at)
                            Exp: {{ $promo->ends_at->format('d M Y') }}
                        @else
                            No Expiry
                        @endif
                    </span>
                </div>
            </div>
            <div class="p-4">
                <div class="flex items-center justify-between mb-3">
                    <span class="font-mono text-lg font-bold">{{ $promo->code }}</span>
                    <button onclick="copyPromo('{{ $promo->code }}')" 
                            class="text-yellow-400 hover:text-yellow-300">
                        <i data-lucide="copy" class="w-5 h-5"></i>
                    </button>
                </div>
                <p class="text-sm text-gray-400 mb-3">
                    @if($promo->game_ids)
                        Selected games only
                    @else
                        Valid for all games
                    @endif
                </p>
                <button class="w-full py-2 bg-yellow-400 text-gray-900 rounded-lg font-semibold hover:bg-yellow-500 transition">
                    Use Now
                </button>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $promos->links() }}
    </div>
    @else
    <div class="glass rounded-xl p-12 text-center">
        <i data-lucide="tag" class="w-16 h-16 mx-auto mb-4 text-gray-500"></i>
        <h3 class="text-xl font-semibold text-gray-200 mb-2">No Active Promos</h3>
        <p class="text-gray-400 mb-6">Check back later for exciting deals and discounts!</p>
    </div>
    @endif

    <!-- Usage History -->
    <div class="mt-12 glass rounded-xl p-6">
        <h2 class="text-xl font-semibold text-yellow-400 mb-4">Promo Usage History</h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b border-gray-700">
                    <tr>
                        <th class="text-left py-3">Promo Code</th>
                        <th class="text-left py-3">Discount</th>
                        <th class="text-left py-3">Used On</th>
                        <th class="text-left py-3">Order</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-gray-800">
                        <td class="py-3">NEWUSER10</td>
                        <td class="py-3">Rp 10.000</td>
                        <td class="py-3">01 Dec 2024</td>
                        <td class="py-3">#INV123456</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function copyPromo(code) {
    navigator.clipboard.writeText(code);
    alert('Promo code copied: ' + code);
}
lucide.createIcons();
</script>
@endsection