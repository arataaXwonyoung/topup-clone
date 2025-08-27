@extends('layouts.app')

@section('title', 'My Wishlist')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="glass rounded-2xl p-6 mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">My Wishlist</h1>
                <p class="text-gray-400">Game favorit yang ingin kamu beli nanti</p>
            </div>
            <div class="text-right">
                <div class="text-2xl font-bold text-yellow-400">{{ $wishlists->total() }}</div>
                <div class="text-sm text-gray-400">Total Games</div>
            </div>
        </div>
    </div>

    @if($wishlists->count() > 0)
        <!-- Wishlist Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
            @foreach($wishlists as $wishlist)
            <div class="game-card glass rounded-xl overflow-hidden group">
                <div class="relative">
                    <img src="{{ asset($wishlist->game->cover_path) }}" 
                         alt="{{ $wishlist->game->name }}"
                         class="w-full h-48 object-cover transition-transform group-hover:scale-105">
                    
                    <!-- Remove from Wishlist Button -->
                    <button onclick="removeFromWishlist({{ $wishlist->game->id }})"
                            class="absolute top-3 right-3 w-8 h-8 bg-red-500 hover:bg-red-600 rounded-full flex items-center justify-center text-white transition">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                    
                    <!-- Hot Badge -->
                    @if($wishlist->game->is_hot)
                    <div class="absolute top-3 left-3 bg-red-500 text-white px-2 py-1 rounded-full text-xs font-bold">
                        HOT
                    </div>
                    @endif
                </div>
                
                <div class="p-4">
                    <h3 class="font-bold text-white mb-2">{{ $wishlist->game->name }}</h3>
                    <p class="text-gray-400 text-sm mb-4">{{ $wishlist->game->publisher ?? 'Publisher' }}</p>
                    
                    <!-- Rating -->
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="flex text-yellow-400">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= floor($wishlist->game->average_rating))
                                    <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                                @else
                                    <i data-lucide="star" class="w-4 h-4"></i>
                                @endif
                            @endfor
                        </div>
                        <span class="text-sm text-gray-400">{{ number_format($wishlist->game->average_rating, 1) }}</span>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex space-x-2">
                        <a href="{{ route('games.show', $wishlist->game->slug) }}" 
                           class="flex-1 bg-yellow-400 text-gray-900 py-2 px-4 rounded-lg font-semibold text-center hover:bg-yellow-500 transition">
                            Top Up Now
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="flex justify-center">
            {{ $wishlists->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="glass rounded-2xl p-12 text-center">
            <div class="w-24 h-24 bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-6">
                <i data-lucide="heart" class="w-12 h-12 text-gray-400"></i>
            </div>
            <h3 class="text-2xl font-bold text-white mb-4">Wishlist Kosong</h3>
            <p class="text-gray-400 mb-8 max-w-md mx-auto">
                Belum ada game favorit di wishlist kamu. Yuk mulai tambahkan game yang ingin kamu beli!
            </p>
            <a href="{{ route('home') }}" 
               class="inline-block bg-yellow-400 text-gray-900 px-8 py-3 rounded-xl font-bold hover:bg-yellow-500 transition">
                <i data-lucide="plus" class="inline w-5 h-5 mr-2"></i>
                Browse Games
            </a>
        </div>
    @endif
</div>

@push('scripts')
<script>
async function removeFromWishlist(gameId) {
    if (!confirm('Hapus game dari wishlist?')) return;
    
    try {
        const response = await fetch('{{ route("user.wishlist.destroy") }}', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ game_id: gameId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert(data.message);
        }
    } catch (error) {
        alert('Terjadi kesalahan');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});
</script>
@endpush
@endsection