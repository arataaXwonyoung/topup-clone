@extends('layouts.app')

@section('title', 'Katalog Game - Aratopup')

@push('styles')
<style>
    /* Takapedia Design Tokens */
    :root {
        --color-base: #0E0E0F;
        --color-surface: #171721;
        --color-elevated: #1F1F2A;
        --color-primary: #FFEA00;
        --color-primary-hover: #FFC700;
        --color-accent: #FFE14D;
        --color-secondary: #7B61FF;
        --color-text-primary: #FFFFFF;
        --color-text-secondary: #E6E6E6;
        --color-text-muted: #A8A8A8;
        --color-border: rgba(255, 255, 255, 0.08);
        --radius-md: 16px;
        --radius-lg: 20px;
        --shadow-soft: 0 8px 24px rgba(0, 0, 0, 0.25);
        --shadow-hover: 0 12px 32px rgba(0, 0, 0, 0.35);
    }

    body {
        background: var(--color-base);
        color: var(--color-text-secondary);
    }

    .filter-section {
        background: var(--color-surface);
        border-radius: var(--radius-lg);
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-soft);
    }

    .search-input {
        background: var(--color-elevated);
        color: var(--color-text-primary);
        border: 2px solid var(--color-border);
        border-radius: var(--radius-md);
        padding: 0.75rem 1rem;
        font-size: 1rem;
        width: 100%;
        transition: all 0.3s ease;
    }

    .search-input:focus {
        outline: none;
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(255, 234, 0, 0.1);
    }

    .filter-select {
        background: var(--color-elevated);
        color: var(--color-text-primary);
        border: 2px solid var(--color-border);
        border-radius: var(--radius-md);
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .filter-select:focus {
        outline: none;
        border-color: var(--color-primary);
    }

    .category-chip {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.3s ease;
        cursor: pointer;
        text-decoration: none;
        border: 1px solid var(--color-border);
        background: var(--color-elevated);
        color: var(--color-text-muted);
    }

    .category-chip:hover {
        background: rgba(255, 234, 0, 0.1);
        color: var(--color-text-secondary);
        border-color: rgba(255, 234, 0, 0.2);
    }

    .category-chip.active {
        background: var(--color-primary);
        color: var(--color-base);
        border-color: var(--color-primary);
        font-weight: 600;
    }

    .game-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
    }

    .game-card {
        position: relative;
        background: var(--color-elevated);
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow-soft);
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .game-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-hover);
    }

    .game-card img {
        aspect-ratio: 16/9;
        width: 100%;
        object-fit: cover;
    }

    .game-info {
        padding: 1.5rem;
    }

    .game-title {
        color: var(--color-text-primary);
        font-weight: 700;
        font-size: 1.125rem;
        margin-bottom: 0.5rem;
    }

    .game-publisher {
        color: var(--color-text-muted);
        font-size: 0.875rem;
        margin-bottom: 1rem;
    }

    .game-stats {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--color-text-muted);
        font-size: 0.75rem;
    }

    .price-range {
        color: var(--color-primary);
        font-weight: 600;
        font-size: 0.875rem;
    }

    .hot-badge {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        background: #FF4757;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .popularity-badge {
        position: absolute;
        top: 0.75rem;
        left: 0.75rem;
        background: rgba(255, 234, 0, 0.9);
        color: var(--color-base);
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
        font-size: 0.7rem;
        font-weight: 600;
        backdrop-filter: blur(4px);
    }

    .pagination-wrapper {
        display: flex;
        justify-content: center;
        margin-top: 3rem;
    }

    .pagination {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .pagination a,
    .pagination span {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: var(--radius-md);
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .pagination a {
        background: var(--color-elevated);
        color: var(--color-text-secondary);
        border: 1px solid var(--color-border);
    }

    .pagination a:hover {
        background: var(--color-primary);
        color: var(--color-base);
        border-color: var(--color-primary);
    }

    .pagination .current {
        background: var(--color-primary);
        color: var(--color-base);
        border: 1px solid var(--color-primary);
    }

    @media (max-width: 768px) {
        .game-grid {
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }
        
        .filter-section {
            padding: 1.5rem;
        }
    }

    @media (max-width: 640px) {
        .game-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen" style="background: var(--color-base);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl md:text-4xl font-bold mb-4" style="color: var(--color-text-primary);">
                🎮 Katalog Game
            </h1>
            <p class="text-lg max-w-2xl mx-auto" style="color: var(--color-text-muted);">
                Temukan semua game favorit kamu dengan harga terbaik dan proses yang instant!
            </p>
        </div>

        <!-- Filters -->
        <div class="filter-section">
            <form method="GET" action="{{ route('games.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: var(--color-text-secondary);">
                            🔍 Cari Game
                        </label>
                        <input type="text" 
                               name="search" 
                               value="{{ $search }}"
                               placeholder="Cari nama game..." 
                               class="search-input">
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: var(--color-text-secondary);">
                            📂 Kategori
                        </label>
                        <select name="category" class="filter-select">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ $category == $cat ? 'selected' : '' }}>
                                {{ ucfirst($cat) }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sort -->
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: var(--color-text-secondary);">
                            🔄 Urutkan
                        </label>
                        <select name="sort" class="filter-select">
                            <option value="popular" {{ $sort == 'popular' ? 'selected' : '' }}>Paling Populer</option>
                            <option value="name" {{ $sort == 'name' ? 'selected' : '' }}>Nama A-Z</option>
                            <option value="latest" {{ $sort == 'latest' ? 'selected' : '' }}>Terbaru</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 justify-center">
                    <button type="submit" class="inline-flex items-center px-6 py-2 rounded-xl font-semibold transition-all duration-300"
                            style="background: var(--color-primary); color: var(--color-base);">
                        🔍 Filter Game
                    </button>
                    <a href="{{ route('games.index') }}" 
                       class="inline-flex items-center px-6 py-2 rounded-xl font-semibold border transition-all duration-300"
                       style="color: var(--color-text-secondary); border-color: var(--color-border);">
                        🔄 Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Quick Categories -->
        @if($categories->count() > 0)
        <div class="mb-8">
            <div class="flex flex-wrap gap-3 justify-center">
                <a href="{{ route('games.index') }}" 
                   class="category-chip {{ !$category ? 'active' : '' }}">
                    Semua
                </a>
                @foreach($categories->take(8) as $cat)
                <a href="{{ route('games.index', ['category' => $cat]) }}" 
                   class="category-chip {{ $category == $cat ? 'active' : '' }}">
                    {{ ucfirst($cat) }}
                </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Results Info -->
        <div class="mb-6">
            <p class="text-center" style="color: var(--color-text-muted);">
                Menampilkan {{ $games->firstItem() ?? 0 }}-{{ $games->lastItem() ?? 0 }} dari {{ $games->total() }} game
                @if($search)
                    untuk "<strong style="color: var(--color-primary);">{{ $search }}</strong>"
                @endif
                @if($category)
                    dalam kategori "<strong style="color: var(--color-primary);">{{ ucfirst($category) }}</strong>"
                @endif
            </p>
        </div>

        <!-- Games Grid -->
        @if($games->count() > 0)
        <div class="game-grid">
            @foreach($games as $game)
            <div class="game-card" onclick="window.location.href='{{ route('games.show', $game->slug) }}'">
                @if($game->cover_path && file_exists(public_path($game->cover_path)))
                    <img src="{{ asset($game->cover_path) }}" alt="{{ $game->name }}">
                @else
                    <div style="aspect-ratio: 16/9; background: linear-gradient(135deg, var(--color-elevated), var(--color-surface)); display: flex; align-items: center; justify-content: center;">
                        <span style="font-size: 3rem;">🎮</span>
                    </div>
                @endif
                
                @if($game->is_hot)
                    <div class="hot-badge">HOT</div>
                @endif
                
                @if($game->orders_count > 0)
                    <div class="popularity-badge">{{ number_format($game->orders_count) }} transaksi</div>
                @endif
                
                <div class="game-info">
                    <h3 class="game-title">{{ $game->name }}</h3>
                    <p class="game-publisher">{{ $game->publisher ?? 'Publisher' }}</p>
                    
                    <div class="game-stats">
                        <div class="stat-item">
                            <span>🎯</span>
                            <span>{{ $game->denominations_count ?? $game->denominations->count() }} item</span>
                        </div>
                        <div class="stat-item">
                            <span>🔥</span>
                            <span>{{ number_format($game->orders_count) }} order</span>
                        </div>
                    </div>
                    
                    @if($game->denominations->count() > 0)
                        @php
                            $minPrice = $game->denominations->min('price');
                            $maxPrice = $game->denominations->max('price');
                        @endphp
                        <div class="price-range">
                            @if($minPrice == $maxPrice)
                                Rp {{ number_format($minPrice, 0, ',', '.') }}
                            @else
                                Rp {{ number_format($minPrice, 0, ',', '.') }} - Rp {{ number_format($maxPrice, 0, ',', '.') }}
                            @endif
                        </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="pagination-wrapper">
            {{ $games->links('pagination::simple-tailwind') }}
        </div>
        @else
        <!-- Empty State -->
        <div class="text-center py-16">
            <div style="font-size: 4rem; margin-bottom: 1rem;">🎮</div>
            <h3 class="text-xl font-semibold mb-4" style="color: var(--color-text-primary);">
                Tidak ada game yang ditemukan
            </h3>
            <p class="mb-6" style="color: var(--color-text-muted);">
                Coba ubah filter atau kata kunci pencarian Anda
            </p>
            <a href="{{ route('games.index') }}" 
               class="inline-flex items-center px-6 py-3 rounded-xl font-semibold transition-all duration-300"
               style="background: var(--color-primary); color: var(--color-base);">
                🔄 Lihat Semua Game
            </a>
        </div>
        @endif
    </div>
</div>
@endsection