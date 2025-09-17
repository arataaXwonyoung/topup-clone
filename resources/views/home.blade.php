@extends('layouts.app')

@section('title', 'Top Up Game Instant & Murah - Takapedia')
@section('description', 'Platform top up game terpercaya #1 di Indonesia. Diamond ML, UC PUBG, Free Fire dengan harga terbaik dan proses instant.')

@push('styles')
<style>
    /* Design System Colors - Use existing CSS variables */

    body {
        background: var(--color-base);
        color: var(--color-text-secondary);
    }

    /* Hero Section with Parallax */
    .hero-section {
        position: relative;
        min-height: 520px;
        background: linear-gradient(135deg, var(--color-base) 0%, #1a1a2e 50%, #16213e 100%);
        overflow: hidden;
        display: flex;
        align-items: center;
        z-index: var(--z-content);
    }

    /* Parallax Layers */
    .parallax-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 120%;
        background: linear-gradient(135deg, 
            rgba(79, 70, 229, 0.3) 0%, 
            rgba(139, 69, 195, 0.2) 25%,
            rgba(59, 130, 246, 0.3) 50%,
            rgba(236, 72, 153, 0.2) 75%,
            rgba(168, 85, 247, 0.3) 100%);
        background-size: 400% 400%;
        animation: parallaxFlow 12s ease-in-out infinite;
        z-index: -4;
    }

    .parallax-layer {
        position: absolute;
        width: 100%;
        height: 100%;
        pointer-events: none;
    }

    /* Geometric Grid Pattern */
    .hero-grid {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: 
            linear-gradient(rgba(255, 213, 74, 0.1) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 213, 74, 0.1) 1px, transparent 1px);
        background-size: 60px 60px;
        animation: gridFloat 8s ease-in-out infinite;
        z-index: -3;
    }

    /* Shooting Stars */
    .shooting-stars {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        z-index: -2;
    }

    .shooting-star {
        position: absolute;
        width: 4px;
        height: 4px;
        background: linear-gradient(90deg, transparent, #fff, transparent);
        border-radius: 50%;
        animation: shoot 3s linear infinite;
    }

    .shooting-star:nth-child(1) { top: 10%; left: -10%; animation-delay: 0s; }
    .shooting-star:nth-child(2) { top: 20%; left: -10%; animation-delay: 1s; }
    .shooting-star:nth-child(3) { top: 30%; left: -10%; animation-delay: 2s; }

    /* Lightning Effects */
    .lightning {
        position: absolute;
        width: 2px;
        height: 100px;
        background: linear-gradient(180deg, transparent, #9ED8FF, transparent);
        box-shadow: 0 0 20px rgba(158, 216, 255, 0.8);
        animation: lightning 4s ease-in-out infinite;
        z-index: -1;
    }

    .lightning:nth-child(1) { top: 20%; right: 20%; animation-delay: 0.5s; }
    .lightning:nth-child(2) { top: 40%; right: 60%; animation-delay: 2.5s; }

    /* Character Artwork */
    .hero-character {
        position: absolute;
        right: 10%;
        top: 50%;
        transform: translateY(-50%);
        width: 300px;
        height: 400px;
        background: url('/images/hero-character.png') no-repeat center/contain;
        animation: float 6s ease-in-out infinite;
        z-index: 1;
    }

    /* Hero Content */
    .hero-content {
        position: relative;
        z-index: 2;
        max-width: 600px;
    }

    .hero-title {
        font-size: 3.5rem;
        font-weight: 800;
        color: var(--color-text-primary);
        text-shadow: 0 0 30px rgba(255, 213, 74, 0.5);
        margin-bottom: 1rem;
        line-height: 1.1;
    }

    .hero-subtitle {
        font-size: 1.2rem;
        color: var(--color-text-secondary);
        margin-bottom: 2rem;
    }

    /* Event Details Table */
    .event-table {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: var(--border-radius);
        overflow: hidden;
        margin: 2rem 0;
    }

    .event-table table {
        width: 100%;
    }

    .event-table th,
    .event-table td {
        padding: 12px 16px;
        text-align: left;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .event-table th {
        background: rgba(255, 213, 74, 0.2);
        color: var(--color-text-primary);
        font-weight: 600;
    }

    /* Hero CTAs */
    .hero-cta {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, #FFD54A 0%, #FFC107 100%);
        color: #1f2937;
        padding: 16px 32px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 1.1rem;
        text-decoration: none;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .btn-primary::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
        transition: left 0.5s;
    }

    .btn-primary:hover::before {
        left: 100%;
    }

    .btn-secondary {
        background: transparent;
        color: var(--color-text-secondary);
        border: 2px solid rgba(255, 255, 255, 0.2);
        padding: 14px 30px;
        border-radius: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-secondary:hover {
        border-color: var(--color-accent);
        color: var(--color-accent);
    }

    /* Category Navigation */
    .category-nav {
        position: sticky;
        top: 80px;
        background: rgba(11, 11, 15, 0.95);
        backdrop-filter: blur(20px);
        border-bottom: 1px solid var(--color-stroke);
        z-index: var(--z-elevated);
        padding: 1rem 0;
    }
    
    @media (max-width: 767px) {
        .category-nav {
            top: 64px;
        }
    }

    .category-tabs {
        display: flex;
        gap: 0.5rem;
        overflow-x: auto;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .category-tabs::-webkit-scrollbar {
        display: none;
    }

    .category-tab {
        flex-shrink: 0;
        padding: 12px 24px;
        background: var(--color-surface);
        color: var(--color-text-secondary);
        border: 1px solid var(--color-stroke);
        border-radius: 25px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    .category-tab.active,
    .category-tab:hover {
        background: var(--color-accent);
        color: #000;
        border-color: var(--color-accent);
        transform: translateY(-2px);
    }

    /* Game Grid - Responsive 6-4-3-2-1 */
    .games-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 1.5rem;
    }

    @media (max-width: 1280px) {
        .games-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    @media (max-width: 1024px) {
        .games-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .games-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 640px) {
        .games-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Game Cards */
    .game-card {
        background: var(--color-surface);
        border: 1px solid var(--color-stroke);
        border-radius: var(--border-radius);
        overflow: hidden;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }

    .game-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        border-color: var(--color-accent);
    }

    .game-card:hover::after {
        opacity: 1;
    }

    .game-card::after {
        content: '';
        position: absolute;
        inset: -2px;
        background: linear-gradient(45deg, var(--color-accent), transparent, var(--color-accent));
        border-radius: inherit;
        opacity: 0;
        z-index: -1;
        transition: opacity 0.3s ease;
    }

    .game-image {
        aspect-ratio: 1/1;
        object-fit: cover;
        width: 100%;
    }

    .game-info {
        padding: 1rem;
    }

    .game-title {
        font-weight: 600;
        color: var(--color-text-primary);
        margin-bottom: 0.5rem;
    }

    .game-badge {
        position: absolute;
        top: 8px;
        right: 8px;
        background: #ff4757;
        color: white;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    /* Recommendations Section */
    .recommendations {
        padding: 4rem 0;
        background: var(--color-surface);
    }

    .section-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 2rem;
        font-weight: 700;
        color: var(--color-text-primary);
        margin-bottom: 2rem;
    }

    .recommendations-grid {
        display: flex;
        gap: 1.5rem;
        overflow-x: auto;
        padding: 1rem 0;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .recommendations-grid::-webkit-scrollbar {
        display: none;
    }

    .recommendation-card {
        flex-shrink: 0;
        width: 280px;
        background: var(--color-base);
        border: 1px solid var(--color-stroke);
        border-radius: var(--border-radius);
        overflow: hidden;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .recommendation-card:hover {
        transform: translateY(-4px);
        border-color: var(--color-accent);
    }

    /* Popular Section */
    .popular-section {
        padding: 4rem 0;
        background: var(--color-base);
    }

    .popular-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .popular-card {
        background: var(--color-surface);
        border: 1px solid var(--color-stroke);
        border-radius: var(--border-radius);
        overflow: hidden;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }

    .popular-card:hover {
        transform: translateY(-4px);
        border-color: var(--color-accent);
    }

    .category-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        background: var(--color-accent);
        color: #000;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    /* Articles Section */
    .articles-section {
        padding: 4rem 0;
        background: var(--color-surface);
    }

    .articles-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2rem;
    }

    @media (max-width: 1024px) {
        .articles-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 640px) {
        .articles-grid {
            grid-template-columns: 1fr;
        }
    }

    .article-card {
        background: var(--color-base);
        border: 1px solid var(--color-stroke);
        border-radius: var(--border-radius);
        overflow: hidden;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }

    .article-card:hover {
        transform: translateY(-4px);
        border-color: var(--color-accent);
    }

    .article-image {
        aspect-ratio: 16/9;
        object-fit: cover;
        width: 100%;
    }

    .article-content {
        padding: 1.5rem;
    }

    .article-title {
        font-weight: 600;
        font-size: 1.1rem;
        color: var(--color-text-primary);
        margin-bottom: 0.75rem;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        background: var(--color-accent);
        color: #000;
        padding: 0.5rem;
        border-radius: 8px;
    }

    .article-preview {
        color: var(--color-text-secondary);
        font-size: 0.9rem;
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin-bottom: 1rem;
    }

    .article-meta {
        display: flex;
        align-items: center;
        gap: 1rem;
        color: var(--color-text-secondary);
        font-size: 0.8rem;
    }

    /* Animations */
    @keyframes parallaxFlow {
        0%, 100% { background-position: 0% 50%; transform: rotate(0deg) scale(1); }
        25% { background-position: 100% 25%; transform: rotate(1deg) scale(1.02); }
        50% { background-position: 100% 75%; transform: rotate(0deg) scale(1); }
        75% { background-position: 0% 75%; transform: rotate(-1deg) scale(1.01); }
    }

    @keyframes gridFloat {
        0%, 100% { transform: translate(0, 0); }
        50% { transform: translate(10px, 5px); }
    }

    @keyframes shoot {
        0% { transform: translate(-10vw, -10vh) rotate(45deg); opacity: 0; }
        20% { opacity: 1; }
        80% { opacity: 1; }
        100% { transform: translate(110vw, 110vh) rotate(45deg); opacity: 0; }
    }

    @keyframes lightning {
        0%, 90%, 100% { opacity: 0; }
        5%, 10% { opacity: 1; transform: scaleY(1) scaleX(1); }
        11% { opacity: 0; transform: scaleY(1.2) scaleX(0.8); }
    }

    @keyframes float {
        0%, 100% { transform: translateY(-50px) rotate(0deg); }
        50% { transform: translateY(-70px) rotate(2deg); }
    }

    /* Mobile Optimizations */
    @media (max-width: 768px) {
        .hero-section {
            min-height: 400px;
            padding: 2rem 1rem;
        }
        
        .hero-title {
            font-size: 2.5rem;
        }
        
        .hero-character {
            display: none;
        }
        
        .hero-cta {
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .category-tabs {
            padding: 0 1rem;
        }
        
        .games-grid {
            gap: 1rem;
        }
    }

    /* Accessibility */
    @media (prefers-reduced-motion: reduce) {
        *,
        *::before,
        *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
    }
</style>
@endpush

@section('content')
<!-- Hero Section with Parallax -->
<section class="hero-section">
    <!-- Parallax Background Layers -->
    <div class="parallax-bg"></div>
    <div class="hero-grid"></div>
    
    <!-- Shooting Stars -->
    <div class="shooting-stars">
        <div class="shooting-star"></div>
        <div class="shooting-star"></div>
        <div class="shooting-star"></div>
    </div>
    
    <!-- Lightning Effects -->
    <div class="lightning"></div>
    <div class="lightning"></div>
    
    <!-- Character Artwork -->
    <div class="hero-character"></div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="hero-content">
            <!-- Event Logo -->
            <div class="mb-4">
                <span class="text-sm font-semibold text-yellow-400">MOBILE LEGENDS</span>
            </div>
            
            <!-- Event Title -->
            <h1 class="hero-title">
                EVENT SPESIAL<br>
                <span style="color: var(--color-accent);">DIAMOND SALE</span>
            </h1>
            
            <!-- Event Date -->
            <p class="text-lg font-medium text-yellow-400 mb-2">15 - 31 Desember 2024</p>
            <p class="hero-subtitle">
                Dapatkan bonus diamond hingga 50% untuk semua pembelian! 
                Promo terbatas, jangan sampai terlewat.
            </p>
            
            <!-- Event Details Table -->
            <div class="event-table">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Bonus</th>
                            <th>Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>15-20 Des</td>
                            <td>+30%</td>
                            <td>Bonus diamond untuk semua pembelian</td>
                        </tr>
                        <tr>
                            <td>21-25 Des</td>
                            <td>+40%</td>
                            <td>Double bonus untuk member VIP</td>
                        </tr>
                        <tr>
                            <td>26-31 Des</td>
                            <td>+50%</td>
                            <td>Mega bonus akhir tahun</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Hero CTAs -->
            <div class="hero-cta">
                <a href="#popular" class="btn-primary">
                    ⚡ Top Up Sekarang
                </a>
                <a href="#event-details" class="btn-secondary">
                    📋 Lihat Detail
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Category Navigation -->
<section class="category-nav">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="category-tabs">
            <button class="category-tab active" data-category="all">Top Up Games</button>
            <button class="category-tab" data-category="mlbb">Specialist MLBB</button>
            <button class="category-tab" data-category="pubg">Specialist PUBGM</button>
            <button class="category-tab" data-category="voucher">Voucher</button>
            <button class="category-tab" data-category="pulsa">Pulsa & Data</button>
            <button class="category-tab" data-category="entertainment">Entertainment</button>
        </div>
    </div>
</section>

<!-- Games Grid Section -->
<section id="popular" class="py-16" style="background: var(--color-base);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="section-title">
            🎮 <span>Game Populer</span>
        </h2>
        
        <div class="games-grid" id="gamesGrid">
            @forelse($games->take(12) as $game)
            <div class="game-card" data-category="{{ $game->category ?? 'all' }}">
                @if($game->is_hot)
                <div class="game-badge">HOT</div>
                @endif
                <img src="{{ $game->cover_path ? asset($game->cover_path) : '/images/placeholder-game.jpg' }}" 
                     alt="{{ $game->name }}" class="game-image">
                <div class="game-info">
                    <h3 class="game-title">{{ $game->name }}</h3>
                    <p class="text-sm text-gray-400">{{ $game->publisher ?? 'Moonton' }}</p>
                </div>
            </div>
            @empty
            <!-- Placeholder Games -->
            @foreach(['Mobile Legends', 'PUBG Mobile', 'Free Fire', 'Genshin Impact', 'Valorant', 'Honor of Kings'] as $gameName)
            <div class="game-card">
                <div class="game-badge">HOT</div>
                <div style="aspect-ratio: 1/1; background: linear-gradient(135deg, var(--color-surface), var(--color-stroke)); display: flex; align-items: center; justify-content: center;">
                    <span style="font-size: 3rem;">🎮</span>
                </div>
                <div class="game-info">
                    <h3 class="game-title">{{ $gameName }}</h3>
                    <p class="text-sm text-gray-400">Moonton</p>
                </div>
            </div>
            @endforeach
            @endforelse
        </div>
    </div>
</section>

<!-- Recommendations Section -->
<section class="recommendations">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="section-title">
            🔥 <span>Rekomendasi</span>
        </h2>
        
        <div class="recommendations-grid">
            @forelse($games->take(6) as $game)
            <div class="recommendation-card">
                <img src="{{ $game->cover_path ? asset($game->cover_path) : '/images/placeholder-game.jpg' }}" 
                     alt="{{ $game->name }}" style="width: 80px; height: 80px; object-fit: cover; margin: 1rem;">
                <div style="padding: 0 1rem 1rem;">
                    <h3 style="font-weight: 600; color: var(--color-text-primary); margin-bottom: 0.5rem;">{{ $game->name }}</h3>
                    <p style="color: var(--color-text-secondary); font-size: 0.9rem;">Mulai dari Rp 1.000</p>
                    <button style="background: var(--color-accent); color: #000; padding: 8px 16px; border: none; border-radius: 8px; font-size: 0.8rem; font-weight: 600; margin-top: 0.5rem; cursor: pointer;">
                        Top Up
                    </button>
                </div>
            </div>
            @empty
            @foreach(['Mobile Legends', 'PUBG Mobile', 'Free Fire', 'Genshin Impact', 'Valorant', 'Arena of Valor'] as $gameName)
            <div class="recommendation-card">
                <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--color-surface), var(--color-stroke)); display: flex; align-items: center; justify-content: center; margin: 1rem; border-radius: 8px;">
                    <span style="font-size: 2rem;">🎮</span>
                </div>
                <div style="padding: 0 1rem 1rem;">
                    <h3 style="font-weight: 600; color: var(--color-text-primary); margin-bottom: 0.5rem;">{{ $gameName }}</h3>
                    <p style="color: var(--color-text-secondary); font-size: 0.9rem;">Mulai dari Rp 1.000</p>
                    <button style="background: var(--color-accent); color: #000; padding: 8px 16px; border: none; border-radius: 8px; font-size: 0.8rem; font-weight: 600; margin-top: 0.5rem; cursor: pointer;">
                        Top Up
                    </button>
                </div>
            </div>
            @endforeach
            @endforelse
        </div>
    </div>
</section>

<!-- Popular Section -->
<section class="popular-section">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="section-title">
            ⭐ <span>Populer Sekarang</span>
        </h2>
        
        <div class="popular-grid">
            @forelse($games->take(8) as $game)
            <div class="popular-card">
                <div class="category-badge">{{ $game->publisher ?? 'Moonton' }}</div>
                <img src="{{ $game->cover_path ? asset($game->cover_path) : '/images/placeholder-game.jpg' }}" 
                     alt="{{ $game->name }}" style="width: 100%; aspect-ratio: 16/9; object-fit: cover;">
                <div style="padding: 1rem;">
                    <h3 style="font-weight: 600; color: var(--color-text-primary); margin-bottom: 0.5rem;">{{ $game->name }}</h3>
                    <p style="color: var(--color-text-secondary); font-size: 0.9rem;">{{ $game->orders_count ?? 0 }} transaksi</p>
                </div>
            </div>
            @empty
            @foreach([
                ['name' => 'Mobile Legends', 'publisher' => 'Moonton', 'count' => '15.2k'],
                ['name' => 'PUBG Mobile', 'publisher' => 'Tencent', 'count' => '12.8k'],
                ['name' => 'Free Fire', 'publisher' => 'Garena', 'count' => '18.5k'],
                ['name' => 'Genshin Impact', 'publisher' => 'HoYoverse', 'count' => '9.1k'],
                ['name' => 'Valorant', 'publisher' => 'Riot Games', 'count' => '7.3k'],
                ['name' => 'Honor of Kings', 'publisher' => 'TiMi Studios', 'count' => '6.8k'],
                ['name' => 'Clash of Clans', 'publisher' => 'Supercell', 'count' => '8.9k'],
                ['name' => 'Arena of Valor', 'publisher' => 'TiMi Studios', 'count' => '5.2k']
            ] as $game)
            <div class="popular-card">
                <div class="category-badge">{{ $game['publisher'] }}</div>
                <div style="width: 100%; aspect-ratio: 16/9; background: linear-gradient(135deg, var(--color-surface), var(--color-stroke)); display: flex; align-items: center; justify-content: center;">
                    <span style="font-size: 3rem;">🎮</span>
                </div>
                <div style="padding: 1rem;">
                    <h3 style="font-weight: 600; color: var(--color-text-primary); margin-bottom: 0.5rem;">{{ $game['name'] }}</h3>
                    <p style="color: var(--color-text-secondary); font-size: 0.9rem;">{{ $game['count'] }} transaksi</p>
                </div>
            </div>
            @endforeach
            @endforelse
        </div>
    </div>
</section>

<!-- Articles Section -->
<section class="articles-section">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="section-title">
            📰 <span>Artikel & News</span>
        </h2>
        
        <div class="articles-grid">
            @forelse($articles->take(3) as $article)
            <article class="article-card">
                <img src="{{ $article->featured_image ?? '/images/placeholder-article.jpg' }}" 
                     alt="{{ $article->title }}" class="article-image">
                <div class="article-content">
                    <h3 class="article-title">{{ $article->title }}</h3>
                    <p class="article-preview">{{ Str::limit($article->excerpt ?? 'Baca artikel menarik tentang dunia gaming dan tips top up game favorit kamu.', 120) }}</p>
                    <div class="article-meta">
                        <span>{{ $article->author ?? 'Admin' }}</span>
                        <span>{{ $article->published_at ? $article->published_at->format('d M Y') : now()->format('d M Y') }}</span>
                        <span>Gaming</span>
                    </div>
                </div>
            </article>
            @empty
            @foreach([
                ['title' => 'Tips Hemat Top Up Diamond Mobile Legends 2024', 'author' => 'Gaming Editor'],
                ['title' => 'Event Terbaru PUBG Mobile: Panduan Lengkap', 'author' => 'News Team'],
                ['title' => 'Cara Aman Top Up Game Online di Indonesia', 'author' => 'Security Team']
            ] as $article)
            <article class="article-card">
                <div style="width: 100%; aspect-ratio: 16/9; background: linear-gradient(135deg, var(--color-surface), var(--color-stroke)); display: flex; align-items: center; justify-content: center;">
                    <span style="font-size: 3rem;">📰</span>
                </div>
                <div class="article-content">
                    <h3 class="article-title">{{ $article['title'] }}</h3>
                    <p class="article-preview">Baca artikel menarik tentang dunia gaming dan tips top up game favorit kamu dengan harga terbaik.</p>
                    <div class="article-meta">
                        <span>{{ $article['author'] }}</span>
                        <span>{{ now()->format('d M Y') }}</span>
                        <span>Gaming</span>
                    </div>
                </div>
            </article>
            @endforeach
            @endforelse
        </div>
    </div>
</section>

@push('scripts')
<script>
// Enhanced Category Tab Functionality
document.addEventListener('DOMContentLoaded', function() {
    const categoryTabs = document.querySelectorAll('.category-tab');
    const gameCards = document.querySelectorAll('.game-card');
    
    // Category filtering
    categoryTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const category = this.dataset.category;
            
            // Update active tab
            categoryTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Filter games with animation
            gameCards.forEach((card, index) => {
                const cardCategory = card.dataset.category;
                const shouldShow = category === 'all' || cardCategory === category;
                
                if (shouldShow) {
                    card.style.display = 'block';
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'scale(1)';
                    }, index * 50); // Stagger animation
                } else {
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        card.style.display = 'none';
                    }, 300);
                }
            });
        });
    });
    
    // Enhanced Parallax with error handling
    const heroSection = document.querySelector('.hero-section');
    const parallaxBg = document.querySelector('.parallax-bg');
    const heroGrid = document.querySelector('.hero-grid');
    const heroCharacter = document.querySelector('.hero-character');
    
    if (heroSection && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        heroSection.addEventListener('mousemove', function(e) {
            try {
                const rect = this.getBoundingClientRect();
                const x = (e.clientX - rect.left) / rect.width;
                const y = (e.clientY - rect.top) / rect.height;
                
                // Move background layers with bounds checking
                if (parallaxBg) {
                    const moveX = Math.max(-30, Math.min(30, (x - 0.5) * 20));
                    const moveY = Math.max(-30, Math.min(30, (y - 0.5) * 20));
                    parallaxBg.style.transform = `translate(${moveX}px, ${moveY}px)`;
                }
                
                // Move grid
                if (heroGrid) {
                    const moveX = Math.max(-15, Math.min(15, (x - 0.5) * 10));
                    const moveY = Math.max(-15, Math.min(15, (y - 0.5) * 10));
                    heroGrid.style.transform = `translate(${moveX}px, ${moveY}px)`;
                }
                
                // Move character
                if (heroCharacter) {
                    const moveX = Math.max(-20, Math.min(20, (x - 0.5) * 15));
                    const moveY = Math.max(-20, Math.min(20, (y - 0.5) * 15));
                    heroCharacter.style.transform = `translateY(-50%) translate(${moveX}px, ${moveY}px)`;
                }
            } catch (error) {
                console.warn('Parallax error:', error);
            }
        });
        
        // Reset parallax on mouse leave
        heroSection.addEventListener('mouseleave', function() {
            if (parallaxBg) parallaxBg.style.transform = 'translate(0, 0)';
            if (heroGrid) heroGrid.style.transform = 'translate(0, 0)';
            if (heroCharacter) heroCharacter.style.transform = 'translateY(-50%) translate(0, 0)';
        });
    }
    
    // Enhanced smooth scrolling
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const target = document.querySelector(targetId);
            
            if (target) {
                const headerOffset = 100; // Account for sticky headers
                const elementPosition = target.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Enhanced game card interactions
    gameCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            if (!this.style.opacity || this.style.opacity === '1') {
                this.style.transform = 'translateY(-8px) scale(1.02)';
                this.style.transition = 'transform 0.3s ease, box-shadow 0.3s ease';
            }
        });
        
        card.addEventListener('mouseleave', function() {
            if (!this.style.opacity || this.style.opacity === '1') {
                this.style.transform = 'translateY(0) scale(1)';
            }
        });
        
        // Add click ripple effect
        card.addEventListener('click', function(e) {
            const ripple = document.createElement('div');
            ripple.className = 'ripple-effect';
            ripple.style.cssText = `
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 213, 74, 0.6);
                transform: scale(0);
                animation: ripple 0.6s linear;
                pointer-events: none;
            `;
            
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            
            this.style.position = 'relative';
            this.appendChild(ripple);
            
            setTimeout(() => {
                if (ripple.parentNode) {
                    ripple.parentNode.removeChild(ripple);
                }
            }, 600);
        });
    });
    
    // Add ripple animation CSS
    const style = document.createElement('style');
    style.textContent = `
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
    
    // Initialize Lucide icons with retry
    function initLucideIcons() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        } else {
            setTimeout(initLucideIcons, 100);
        }
    }
    initLucideIcons();
});
</script>
@endpush
@endsection