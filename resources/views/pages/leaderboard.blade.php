@extends('layouts.app')

@section('title', 'Leaderboard - Top Spender')

@push('styles')
<style>
    /* Leaderboard specific fixes */
    .leaderboard-table {
        position: relative !important;
        z-index: 100 !important;
        visibility: visible !important;
        opacity: 1 !important;
        display: block !important;
    }
    
    .leaderboard-table table,
    .leaderboard-table tbody,
    .leaderboard-table tr,
    .leaderboard-table td,
    .leaderboard-table th {
        position: relative !important;
        z-index: 100 !important;
        visibility: visible !important;
        opacity: 1 !important;
        display: table !important;
    }
    
    .leaderboard-table tr {
        display: table-row !important;
    }
    
    .leaderboard-table td,
    .leaderboard-table th {
        display: table-cell !important;
    }
    
    .glass {
        position: relative !important;
        z-index: 10 !important;
        background: rgba(30, 30, 40, 0.95) !important;
        backdrop-filter: blur(10px) !important;
    }
    
    /* Fix hidden elements */
    .leaderboard-content {
        background: rgba(30, 30, 40, 0.95) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        position: relative !important;
        z-index: 50 !important;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-8 py-4 sm:py-8">
    <div class="glass rounded-xl p-4 sm:p-6 mb-6 sm:mb-8 text-center">
        <h1 class="text-2xl sm:text-3xl font-bold text-yellow-400 mb-4">🏆 Leaderboard</h1>
        <p class="text-gray-400 text-sm sm:text-base">Top Spender Bulan {{ now()->format('F Y') }}</p>
    </div>

    <div class="glass leaderboard-content rounded-xl p-4 sm:p-6">
        @if($topSpenders && $topSpenders->count() > 0)
        <div class="leaderboard-table overflow-x-auto">
            <table class="w-full text-sm sm:text-base">
                <thead>
                    <tr class="border-b border-gray-700">
                        <th class="text-left py-2 sm:py-3 px-2 sm:px-4 text-xs sm:text-sm">Rank</th>
                        <th class="text-left py-2 sm:py-3 px-2 sm:px-4 text-xs sm:text-sm">User</th>
                        <th class="text-center py-2 sm:py-3 px-2 sm:px-4 text-xs sm:text-sm hidden sm:table-cell">Total Order</th>
                        <th class="text-right py-2 sm:py-3 px-2 sm:px-4 text-xs sm:text-sm">Total Spent</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topSpenders as $index => $spender)
                    <tr class="border-b border-gray-800 hover:bg-gray-800/50">
                        <td class="py-2 sm:py-3 px-2 sm:px-4">
                            @if($index == 0)
                                <span class="text-2xl">🥇</span>
                            @elseif($index == 1)
                                <span class="text-2xl">🥈</span>
                            @elseif($index == 2)
                                <span class="text-2xl">🥉</span>
                            @else
                                <span class="text-gray-400">#{{ $index + 1 }}</span>
                            @endif
                        </td>
                        <td class="py-2 sm:py-3 px-2 sm:px-4">
                            <div>
                                <div class="font-semibold text-xs sm:text-sm">{{ substr($spender->email, 0, 3) }}***</div>
                                <div class="text-xs text-gray-400 hidden sm:block">{{ substr($spender->whatsapp, 0, 4) }}****</div>
                            </div>
                        </td>
                        <td class="py-2 sm:py-3 px-2 sm:px-4 text-center hidden sm:table-cell">
                            <span class="px-2 py-1 bg-gray-700 rounded text-xs">{{ $spender->order_count }}</span>
                        </td>
                        <td class="py-2 sm:py-3 px-2 sm:px-4 text-right">
                            <span class="text-yellow-400 font-bold text-xs sm:text-sm">
                                Rp {{ number_format($spender->total_spent, 0, ',', '.') }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-200">Belum ada data</h3>
            <p class="mt-1 text-sm text-gray-400">Leaderboard akan muncul setelah ada transaksi.</p>
        </div>
        @endif
    </div>

    <!-- Info Box -->
    <div class="mt-8 glass rounded-xl p-6">
        <h2 class="text-lg font-semibold text-yellow-400 mb-3">🎁 Hadiah Top Spender</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gray-800 rounded-lg p-4">
                <div class="text-2xl mb-2">🥇 Rank 1</div>
                <p class="text-sm text-gray-400">Bonus Saldo Rp 100.000</p>
            </div>
            <div class="bg-gray-800 rounded-lg p-4">
                <div class="text-2xl mb-2">🥈 Rank 2</div>
                <p class="text-sm text-gray-400">Bonus Saldo Rp 50.000</p>
            </div>
            <div class="bg-gray-800 rounded-lg p-4">
                <div class="text-2xl mb-2">🥉 Rank 3</div>
                <p class="text-sm text-gray-400">Bonus Saldo Rp 25.000</p>
            </div>
        </div>
        <p class="text-xs text-gray-500 mt-4">*Hadiah akan diberikan setiap awal bulan</p>
    </div>
</div>
@endsection