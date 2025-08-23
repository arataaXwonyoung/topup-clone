@extends('layouts.app')

@section('title', 'Top Up Game Instant & Murah')

@section('content')
<!-- Hero Section -->
<section class="relative min-h-[500px] flex items-center justify-center overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-purple-900/20 via-blue-900/20 to-pink-900/20"></div>
    <div class="relative z-10 text-center px-4">
        <h1 class="text-5xl md:text-7xl font-bold mb-6 bg-gradient-to-r from-yellow-400 via-pink-500 to-purple-600 bg-clip-text text-transparent animate-gradient">
            Top Up Game Instant & Murah
        </h1>
        <p class="text-xl text-gray-300 mb-8 max-w-2xl mx-auto">
            Dapatkan diamond, UC, dan item game favorit kamu dengan harga terbaik!
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="#games" class="px-8 py-3 bg-yellow-400 text-gray-900 rounded-lg font-semibold hover:bg-yellow-500 transform hover:scale-105 transition neon-glow">
                <i data-lucide="zap" class="inline w-5 h-5 mr-2"></i>
                Top Up Sekarang
            </a>
            <a href="{{ route('transactions.check') }}" class="px-8 py-3 glass border border-gray-600 rounded-lg font-semibold hover:bg-white/10 transform hover:scale-105 transition">
                <i data-lucide="search" class="inline w-5 h-5 mr-2"></i>
                Cek Transaksi
            </a>
        </div>
    </div>
    
    <!-- Animated Background Elements -->
    <div class="absolute top-20 left-10 w-72 h-72 bg-purple-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob"></div>
    <div class="absolute top-40 right-10 w-72 h-72 bg-yellow-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-2000"></div>
    <div class="absolute -bottom-8 left-20 w-72 h-72 bg-pink-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-4000"></div>
</section>

<!-- Stats Section -->
<section class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="glass rounded-xl p-6 text-center transform hover:scale-105 transition">
                <div class="text-3xl font-bold text-yellow-400 mb-2">10K+</div>
                <div class="text-sm text-gray-400">Happy Customers</div>
            </div>
            <div class="glass rounded-xl p-6 text-center transform hover:scale-105 transition">
                <div class="text-3xl font-bold text-green-400 mb-2">24/7</div>
                <div class="text-sm text-gray-400">Instant Process</div>
            </div>
            <div class="glass rounded-xl p-6 text-center transform hover:scale-105 transition">
                <div class="text-3xl font-bold text-blue-400 mb-2">100%</div>
                <div class="text-sm text-gray-400">Secure Payment</div>
            </div>
            <div class="glass rounded-xl p-6 text-center transform hover:scale-105 transition">
                <div class="text-3xl font-bold text-purple-400 mb-2">50+</div>
                <div class="text-sm text-gray-400">Games Available</div>
            </div>
        </div>
    </div>
</section>

<!-- Games Section -->
<section id="games" class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold mb-4 bg-gradient-to-r from-yellow-400 to-orange-500 bg-clip-text text-transparent">
                Pilih Game Favorit Kamu
            </h2>
            <p class="text-gray-400">Top up diamond, UC, dan item game dengan proses instant!</p>
        </div>

        <!-- Category Tabs -->
        <div class="flex flex-wrap justify-center gap-4 mb-8">
            @foreach(['all' => 'Semua', 'games' => 'Mobile Games', 'voucher' => 'Voucher', 'entertainment' => 'Entertainment'] as $key => $label)
            <button onclick="filterGames('{{ $key }}')" 
                    class="category-tab px-6 py-2 glass rounded-lg font-semibold hover:bg-yellow-400 hover:text-gray-900 transition {{ $key === 'all' ? 'bg-yellow-400 text-gray-900' : '' }}"
                    data-category="{{ $key }}">
                {{ $label }}
            </button>
            @endforeach
        </div>

        <!-- Games Grid -->
        @if($games && $games->count() > 0)
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
            @foreach($games as $game)
            <a href="{{ route('games.show', $game->slug) }}" 
               class="game-card group relative glass rounded-xl overflow-hidden transform hover:scale-105 transition-all duration-300"
               data-category="{{ $game->category }}">
                @if($game->is_hot)
                <div class="absolute top-2 right-2 z-10 bg-red-500 text-white text-xs px-2 py-1 rounded font-bold animate-pulse">
                    HOT
                </div>
                @endif
                <div class="aspect-square bg-gradient-to-br from-gray-800 to-gray-900 p-4 flex items-center justify-center">
                    @if($game->cover_path && file_exists(public_path($game->cover_path)))
                        <img src="{{ asset($game->cover_path) }}" alt="{{ $game->name }}" class="w-full h-full object-cover rounded-lg">
                    @else
                        <div class="text-center">
                            <i data-lucide="gamepad-2" class="w-12 h-12 mx-auto mb-2 text-gray-600"></i>
                            <span class="text-xs text-gray-500">{{ $game->name }}</span>
                        </div>
                    @endif
                </div>
                <div class="p-3">
                    <h3 class="font-semibold text-sm text-center group-hover:text-yellow-400 transition">
                        {{ $game->name }}
                    </h3>
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-yellow-400/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            </a>
            @endforeach
        </div>
        @else
        <div class="text-center py-12">
            <i data-lucide="inbox" class="w-16 h-16 mx-auto mb-4 text-gray-600"></i>
            <h3 class="text-xl font-semibold text-gray-400 mb-2">Tidak ada game tersedia</h3>
            <p class="text-gray-500">Silakan cek kembali nanti</p>
        </div>
        @endif
    </div>
</section>

<!-- Features Section -->
<section class="py-16 bg-gradient-to-b from-transparent to-purple-900/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-center mb-12 bg-gradient-to-r from-blue-400 to-purple-500 bg-clip-text text-transparent">
            Kenapa Pilih Aratopup?
        </h2>
        <div class="grid md:grid-cols-3 gap-8">
            <div class="glass rounded-xl p-6 text-center transform hover:scale-105 transition">
                <div class="w-16 h-16 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="zap" class="w-8 h-8 text-white"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Proses Instant</h3>
                <p class="text-gray-400">Top up diamond dan UC langsung masuk ke akun game kamu dalam hitungan detik</p>
            </div>
            <div class="glass rounded-xl p-6 text-center transform hover:scale-105 transition">
                <div class="w-16 h-16 bg-gradient-to-br from-green-400 to-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="shield-check" class="w-8 h-8 text-white"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">100% Aman</h3>
                <p class="text-gray-400">Pembayaran terenkripsi dan dijamin keamanannya dengan berbagai metode</p>
            </div>
            <div class="glass rounded-xl p-6 text-center transform hover:scale-105 transition">
                <div class="w-16 h-16 bg-gradient-to-br from-purple-400 to-pink-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="headphones" class="w-8 h-8 text-white"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Support 24/7</h3>
                <p class="text-gray-400">Tim customer service siap membantu kapan saja kamu butuhkan</p>
            </div>
        </div>
    </div>
</section>

<!-- Payment Methods -->
<section class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-center mb-8">Metode Pembayaran</h2>
        <div class="glass rounded-xl p-8">
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-6">
                @foreach(['QRIS', 'GoPay', 'DANA', 'OVO', 'ShopeePay', 'BCA', 'BNI', 'BRI', 'Mandiri', 'Alfamart', 'Indomaret', 'LinkAja'] as $payment)
                <div class="flex items-center justify-center p-4 bg-gray-800 rounded-lg hover:bg-gray-700 transition">
                    <span class="text-sm font-medium">{{ $payment }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
function filterGames(category) {
    // Update active tab
    document.querySelectorAll('.category-tab').forEach(tab => {
        tab.classList.remove('bg-yellow-400', 'text-gray-900');
        if (tab.dataset.category === category) {
            tab.classList.add('bg-yellow-400', 'text-gray-900');
        }
    });
    
    // Filter games
    document.querySelectorAll('.game-card').forEach(card => {
        if (category === 'all' || card.dataset.category === category) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>
@endpush