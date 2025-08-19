@extends('layouts.app')

@section('title', 'Top Up Game Online Termurah - Takapedia Clone')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    @if(isset($error))
    <div class="bg-red-500 text-white p-4 rounded-lg mb-6">
        {{ $error }}
    </div>
    @endif
    
    <!-- Debug Info (only in development) -->
    @if(config('app.debug'))
    <div class="bg-gray-800 p-4 rounded-lg mb-6">
        <h3 class="text-yellow-400 font-bold mb-2">Debug Info:</h3>
        <p>Total Games: {{ \App\Models\Game::count() }}</p>
        <p>Active Games: {{ \App\Models\Game::active()->count() }}</p>
        <p>Current Category: {{ $category ?? 'none' }}</p>
        <p>Games on this page: {{ $games->count() ?? 0 }}</p>
    </div>
    @endif
    
    <!-- Hero Banner with Parallax -->
    <div class="relative mb-8 rounded-xl overflow-hidden" style="height: 400px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="absolute inset-0 bg-gradient-to-r from-purple-900 to-blue-900 opacity-75"></div>
        <div class="absolute inset-0 flex items-center justify-center">
            <div class="text-center">
                <h1 class="text-5xl font-bold text-white mb-4" style="text-shadow: 0 0 20px rgba(255, 235, 59, 0.5);">
                    Top Up Game Instant & Murah
                </h1>
                <p class="text-xl text-gray-200">Dapatkan diamond, UC, dan item game favorit kamu dengan harga terbaik!</p>
            </div>
        </div>
    </div>
    
    <!-- Category Tabs -->
    <div class="flex flex-wrap gap-2 mb-8">
        <a href="?category=games" 
           class="px-6 py-3 rounded-lg {{ request('category', 'games') == 'games' ? 'bg-yellow-400 text-gray-900' : 'bg-gray-800 text-white hover:bg-gray-700' }} transition">
            Top Up Games
        </a>
        <a href="?category=voucher" 
           class="px-6 py-3 rounded-lg {{ request('category') == 'voucher' ? 'bg-yellow-400 text-gray-900' : 'bg-gray-800 text-white hover:bg-gray-700' }} transition">
            Voucher
        </a>
        <a href="?category=pulsa" 
           class="px-6 py-3 rounded-lg {{ request('category') == 'pulsa' ? 'bg-yellow-400 text-gray-900' : 'bg-gray-800 text-white hover:bg-gray-700' }} transition">
            Pulsa & Tagihan
        </a>
        <a href="?category=entertainment" 
           class="px-6 py-3 rounded-lg {{ request('category') == 'entertainment' ? 'bg-yellow-400 text-gray-900' : 'bg-gray-800 text-white hover:bg-gray-700' }} transition">
            Entertainment
        </a>
    </div>
    
    <!-- Popular Section -->
    @if(isset($popularGames) && $popularGames->count() > 0)
    <div class="mb-12">
        <h2 class="text-2xl font-bold text-yellow-400 mb-6 flex items-center">
            <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
            POPULER SEKARANG!
        </h2>
        <p class="text-gray-400 mb-6">Berikut adalah beberapa produk yang paling populer saat ini.</p>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($popularGames as $game)
            <a href="{{ route('games.show', $game->slug) }}" 
               class="bg-gray-800 rounded-xl overflow-hidden hover:transform hover:scale-105 transition-all duration-300 group">
                <div class="relative aspect-w-16 aspect-h-9">
                    @if($game->cover_path && file_exists(public_path($game->cover_path)))
                        <img src="{{ asset($game->cover_path) }}" 
                             alt="{{ $game->name }}" 
                             class="w-full h-48 object-cover">
                    @else
                        <div class="w-full h-48 bg-gradient-to-br from-purple-600 to-blue-600 flex items-center justify-center">
                            <span class="text-white text-2xl font-bold">{{ substr($game->name, 0, 2) }}</span>
                        </div>
                    @endif
                    @if($game->is_hot)
                    <span class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded text-xs font-bold">HOT</span>
                    @endif
                </div>
                <div class="p-4 bg-gradient-to-t from-yellow-400 to-yellow-500">
                    <h3 class="font-semibold text-gray-900">{{ $game->name }}</h3>
                    <p class="text-sm text-gray-800">{{ $game->publisher }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif
    
    <!-- All Games Grid -->
    <div>
        <h2 class="text-2xl font-bold text-yellow-400 mb-6">Semua Game</h2>
        
        @if(isset($games) && $games->count() > 0)
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach($games as $game)
            <a href="{{ route('games.show', $game->slug) }}" 
               class="bg-gray-800 rounded-xl overflow-hidden hover:transform hover:scale-105 transition-all duration-300 group">
                <div class="relative">
                    @if($game->cover_path && file_exists(public_path($game->cover_path)))
                        <img src="{{ asset($game->cover_path) }}" 
                             alt="{{ $game->name }}" 
                             class="w-full h-40 object-cover group-hover:opacity-90">
                    @else
                        <div class="w-full h-40 bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center">
                            <span class="text-white text-xl font-bold">{{ substr($game->name, 0, 2) }}</span>
                        </div>
                    @endif
                    @if($game->is_hot)
                    <span class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded text-xs font-bold">HOT</span>
                    @endif
                </div>
                <div class="p-3 bg-gradient-to-t from-yellow-400/20 to-transparent">
                    <h3 class="font-semibold text-sm text-white">{{ $game->name }}</h3>
                    <p class="text-xs text-gray-400">{{ $game->publisher }}</p>
                </div>
            </a>
            @endforeach
        </div>
        
        <!-- Pagination -->
        <div class="mt-8">
            {{ $games->links() }}
        </div>
        @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-200">Tidak ada game</h3>
            <p class="mt-1 text-sm text-gray-400">Belum ada game yang tersedia untuk kategori ini.</p>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Parallax effect for hero banner
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const parallax = document.querySelector('.parallax');
        if (parallax) {
            parallax.style.transform = `translateY(${scrolled * 0.5}px)`;
        }
    });
    
    // Debug info
    console.log('Page loaded successfully');
    @if(config('app.debug'))
    console.log('Games data:', @json($games ?? []));
    @endif
</script>
@endpush