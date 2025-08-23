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
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @php
            // Sample promo data - replace with actual data from controller
            $promos = [
                ['code' => 'NEWUSER10', 'value' => '10%', 'min' => 50000, 'expires' => '2024-12-31'],
                ['code' => 'WEEKEND15', 'value' => '15%', 'min' => 100000, 'expires' => '2024-12-15'],
                ['code' => 'CASHBACK5K', 'value' => 'Rp 5.000', 'min' => 75000, 'expires' => '2024-12-20'],
            ];
        @endphp

        @foreach($promos as $promo)
        <div class="glass rounded-xl overflow-hidden hover:transform hover:scale-105 transition">
            <div class="bg-gradient-to-r from-yellow-400 to-orange-400 p-4">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-900 font-bold text-2xl">{{ $promo['value'] }} OFF</p>
                        <p class="text-gray-800 text-sm">Min. Rp {{ number_format($promo['min'], 0, ',', '.') }}</p>
                    </div>
                    <span class="bg-white/20 px-2 py-1 rounded text-xs text-gray-900">
                        Exp: {{ $promo['expires'] }}
                    </span>
                </div>
            </div>
            <div class="p-4">
                <div class="flex items-center justify-between mb-3">
                    <span class="font-mono text-lg font-bold">{{ $promo['code'] }}</span>
                    <button onclick="copyPromo('{{ $promo['code'] }}')" 
                            class="text-yellow-400 hover:text-yellow-300">
                        <i data-lucide="copy" class="w-5 h-5"></i>
                    </button>
                </div>
                <p class="text-sm text-gray-400 mb-3">Valid for all games</p>
                <button class="w-full py-2 bg-yellow-400 text-gray-900 rounded-lg font-semibold hover:bg-yellow-500 transition">
                    Use Now
                </button>
            </div>
        </div>
        @endforeach
    </div>

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