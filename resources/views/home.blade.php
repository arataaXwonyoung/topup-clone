@extends('layouts.app')

@section('title', 'Top Up Game Instant & Murah - Aratopup')

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

    /* Hero Section - Simple Takapedia Style */
    .hero-section {
        background: linear-gradient(135deg, var(--color-base) 0%, var(--color-surface) 100%);
        padding: 4rem 0 2rem;
        position: relative;
    }

    /* Game Search Styles */
    #gameSearch {
        transition: all 0.3s ease;
    }

    #gameSearch:focus {
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(255, 234, 0, 0.1);
    }

    .search-result-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.75rem 1rem;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }

    .search-result-item:hover {
        background: rgba(255, 234, 0, 0.1);
    }

    .search-result-image {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        object-fit: cover;
        background: var(--color-surface);
    }

    .search-result-content {
        flex: 1;
    }

    .search-result-name {
        font-weight: 600;
        color: var(--color-text-primary);
        font-size: 0.875rem;
    }

    .search-result-publisher {
        color: var(--color-text-muted);
        font-size: 0.75rem;
    }

    /* Payment Method Badges */
    .payment-method-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(255, 234, 0, 0.1);
        border: 1px solid rgba(255, 234, 0, 0.2);
        color: var(--color-text-secondary);
        padding: 0.5rem 0.75rem;
        border-radius: var(--radius-md);
        font-size: 0.75rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .payment-method-badge:hover {
        background: rgba(255, 234, 0, 0.2);
        border-color: var(--color-primary);
        transform: translateY(-2px);
    }

    .payment-icon {
        font-size: 0.875rem;
    }

    /* Banner Slider Styles */
    .banner-slider {
        position: relative;
        max-width: 1200px;
        margin: 0 auto;
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow-soft);
        background: var(--color-elevated);
    }

    .banner-slide {
        display: none;
        position: relative;
        width: 100%;
        aspect-ratio: 16/6;
        cursor: pointer;
    }

    .banner-slide.active {
        display: block;
    }

    .banner-slide img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .banner-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
        color: white;
        padding: 2rem;
    }

    .banner-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .banner-indicators {
        position: absolute;
        bottom: 1rem;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 0.5rem;
    }

    .banner-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.5);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .banner-indicator.active {
        background: var(--color-primary);
        transform: scale(1.2);
    }

    .banner-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(0, 0, 0, 0.5);
        color: white;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        opacity: 0.7;
    }

    .banner-nav:hover {
        opacity: 1;
        background: rgba(0, 0, 0, 0.8);
    }

    .banner-nav.prev {
        left: 1rem;
    }

    .banner-nav.next {
        right: 1rem;
    }

    /* Game Cards with Grayscale Effect */

    .game-card img {
        aspect-ratio: 16/9;
        width: 100%;
        object-fit: cover;
        filter: grayscale(100%) brightness(0.9);
        transition: filter 0.3s ease;
    }

    .game-card:hover img {
        filter: grayscale(60%) brightness(1);
    }

    .game-card.active img {
        filter: none;
    }

    .game-card.active {
        ring: 2px solid var(--color-accent);
        box-shadow: var(--shadow-hover), 0 0 0 2px var(--color-accent);
    }

    .game-label {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(to right, var(--color-primary), var(--color-accent));
        color: var(--color-base);
        padding: 0.75rem 1rem;
        font-weight: 700;
        font-size: 0.875rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .game-vendor {
        font-size: 0.75rem;
        opacity: 0.8;
        font-weight: 500;
    }

    /* Category Chips */
    .chip {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.3s ease;
        cursor: pointer;
        border: 1px solid var(--color-border);
        background: var(--color-elevated);
        color: var(--color-text-muted);
    }

    .chip:hover {
        background: rgba(255, 234, 0, 0.1);
        color: var(--color-text-secondary);
        border-color: rgba(255, 234, 0, 0.2);
    }

    .chip.active {
        background: var(--color-primary);
        color: var(--color-base);
        border-color: var(--color-primary);
        font-weight: 600;
    }

    /* Poster Cards */
    .poster-card {
        position: relative;
        background: var(--color-elevated);
        border-radius: var(--radius-md);
        overflow: hidden;
        box-shadow: var(--shadow-soft);
    }

    .poster-card img {
        aspect-ratio: 3/4;
        width: 100%;
        object-fit: cover;
    }

    .poster-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(transparent, rgba(255, 234, 0, 0.9));
        padding: 1rem;
        color: var(--color-base);
    }

    .hot-badge {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        background: #FF4757;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .popularity-badge {
        position: absolute;
        top: 0.5rem;
        left: 0.5rem;
        background: rgba(255, 234, 0, 0.9);
        color: var(--color-base);
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
        font-size: 0.7rem;
        font-weight: 600;
        backdrop-filter: blur(4px);
    }

    /* Article Cards */
    .article-card {
        background: var(--color-elevated);
        border-radius: var(--radius-md);
        overflow: hidden;
        box-shadow: var(--shadow-soft);
        transition: all 0.3s ease;
    }

    .article-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-hover);
    }

    .article-image {
        position: relative;
        aspect-ratio: 16/9;
        overflow: hidden;
    }

    .article-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .article-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(transparent, rgba(0, 0, 0, 0.7));
        padding: 1.5rem 1rem 1rem;
    }

    .article-title {
        color: var(--color-text-primary);
        font-weight: 600;
        font-size: 1rem;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .article-meta {
        color: var(--color-text-muted);
        font-size: 0.75rem;
        margin-top: 0.5rem;
        padding-top: 0.5rem;
        border-top: 1px solid var(--color-border);
    }

    /* Section Headers */
    .section-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }

    .section-title {
        color: var(--color-text-primary);
        font-size: 1.5rem;
        font-weight: 700;
    }

    .section-subtitle {
        color: var(--color-text-muted);
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    /* Game Title Popup Animation */
    .game-title-popup {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(0);
        background: linear-gradient(135deg, var(--color-primary), var(--color-accent));
        color: var(--color-base);
        padding: 2rem 3rem;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-hover), 0 0 50px rgba(255, 234, 0, 0.3);
        z-index: 1000;
        pointer-events: none;
        font-size: 1.5rem;
        font-weight: 700;
        text-align: center;
        max-width: 80vw;
        word-wrap: break-word;
        opacity: 0;
        transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    .game-title-popup.show {
        transform: translate(-50%, -50%) scale(1);
        opacity: 1;
    }

    .game-title-popup::before {
        content: '';
        position: absolute;
        top: -2px;
        left: -2px;
        right: -2px;
        bottom: -2px;
        background: linear-gradient(45deg, var(--color-primary), var(--color-accent), var(--color-secondary));
        border-radius: var(--radius-lg);
        z-index: -1;
        opacity: 0;
        animation: borderGlow 2s ease-in-out infinite alternate;
    }

    @keyframes borderGlow {
        from {
            opacity: 0;
        }
        to {
            opacity: 0.6;
        }
    }

    .game-title-popup.show::before {
        opacity: 0.6;
    }

    /* Floating particles animation */
    .popup-particle {
        position: absolute;
        width: 6px;
        height: 6px;
        background: var(--color-accent);
        border-radius: 50%;
        pointer-events: none;
        animation: floatUp 1.5s ease-out forwards;
    }

    @keyframes floatUp {
        0% {
            transform: translateY(0) scale(1);
            opacity: 1;
        }
        100% {
            transform: translateY(-100px) scale(0);
            opacity: 0;
        }
    }

    /* Enhanced game card hover effect */
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

    .game-card.clicked {
        animation: gameCardClick 0.6s ease-out;
    }

    @keyframes gameCardClick {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
        100% {
            transform: scale(1);
        }
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .hero-section {
            padding: 2rem 0 1rem;
        }
        
        .game-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        
        .article-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .game-title-popup {
            font-size: 1.25rem;
            padding: 1.5rem 2rem;
            max-width: 90vw;
        }
    }

    @media (max-width: 640px) {
        .game-grid {
            grid-template-columns: 1fr;
        }
        
        .chip {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
        }
        
        .game-title-popup {
            font-size: 1rem;
            padding: 1rem 1.5rem;
            max-width: 95vw;
        }
    }

    /* Welcome Notification Popup */
    .welcome-notification {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(10px);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: all 0.4s ease;
        padding: 1rem;
    }

    .welcome-notification.show {
        opacity: 1;
        visibility: visible;
    }

    .welcome-notification-content {
        background: linear-gradient(135deg, var(--color-surface), var(--color-elevated));
        border: 2px solid var(--color-primary);
        border-radius: var(--radius-lg);
        padding: 2rem;
        max-width: 500px;
        width: 100%;
        position: relative;
        transform: scale(0.8) translateY(20px);
        transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        box-shadow: var(--shadow-hover), 0 0 30px rgba(255, 234, 0, 0.2);
    }

    .welcome-notification.show .welcome-notification-content {
        transform: scale(1) translateY(0);
    }

    .notification-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--color-border);
    }

    .notification-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: var(--color-text-primary);
        font-size: 1.5rem;
        font-weight: 700;
    }

    .notification-close {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid var(--color-border);
        color: var(--color-text-muted);
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 1.2rem;
        font-weight: bold;
    }

    .notification-close:hover {
        background: var(--color-primary);
        color: var(--color-base);
        transform: rotate(90deg);
    }

    .notification-body {
        margin-bottom: 1.5rem;
    }

    .notification-text {
        color: var(--color-text-secondary);
        line-height: 1.6;
        margin-bottom: 1rem;
    }

    .notification-highlight {
        background: linear-gradient(135deg, var(--color-primary), var(--color-accent));
        color: var(--color-base);
        padding: 1rem;
        border-radius: var(--radius-md);
        margin: 1rem 0;
        font-weight: 600;
        text-align: center;
    }

    .notification-features {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 0.75rem;
        margin: 1rem 0;
    }

    .notification-feature {
        background: rgba(255, 234, 0, 0.1);
        border: 1px solid rgba(255, 234, 0, 0.2);
        border-radius: var(--radius-md);
        padding: 0.75rem;
        text-align: center;
        color: var(--color-text-secondary);
        font-size: 0.875rem;
    }

    .notification-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
    }

    .notification-btn {
        padding: 0.75rem 1.5rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .notification-btn-primary {
        background: var(--color-primary);
        color: var(--color-base);
        border: 2px solid var(--color-primary);
    }

    .notification-btn-primary:hover {
        background: var(--color-primary-hover);
        transform: translateY(-2px);
        box-shadow: var(--shadow-soft);
    }

    .notification-btn-secondary {
        background: transparent;
        color: var(--color-text-secondary);
        border: 2px solid var(--color-border);
    }

    .notification-btn-secondary:hover {
        background: rgba(255, 255, 255, 0.05);
        border-color: var(--color-primary);
        color: var(--color-primary);
    }

    /* Mobile responsive */
    @media (max-width: 640px) {
        .welcome-notification-content {
            padding: 1.5rem;
            margin: 1rem;
        }
        
        .notification-title {
            font-size: 1.25rem;
        }
        
        .notification-features {
            grid-template-columns: 1fr;
        }
        
        .notification-actions {
            flex-direction: column;
        }
        
        .notification-btn {
            text-align: center;
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
<!-- Welcome Notification Popup -->
<div id="welcomeNotification" class="welcome-notification" onclick="handleNotificationBackdropClick(event)">
    <div class="welcome-notification-content" onclick="event.stopPropagation()">
        <div class="notification-header">
            <div class="notification-title">
                <span>🎉</span>
                <span>Selamat Datang di Takapedia!</span>
            </div>
            <button class="notification-close" onclick="closeWelcomeNotification()">×</button>
        </div>
        
        <div class="notification-body">
            <p class="notification-text">
                Platform top up game terpercaya dengan harga terbaik dan proses instant!
            </p>
            
            <div class="notification-highlight">
                🔥 PROMO SPESIAL: Diskon hingga 20% untuk semua game populer!
            </div>
            
            <div class="notification-features">
                <div class="notification-feature">
                    <div>⚡</div>
                    <div>Proses Instant</div>
                </div>
                <div class="notification-feature">
                    <div>💰</div>
                    <div>Harga Terbaik</div>
                </div>
                <div class="notification-feature">
                    <div>🔒</div>
                    <div>100% Aman</div>
                </div>
                <div class="notification-feature">
                    <div>🎮</div>
                    <div>All Games</div>
                </div>
            </div>
            
            <p class="notification-text" style="font-size: 0.875rem; color: var(--color-text-muted);">
                Bergabunglah dengan lebih dari 10.000+ gamers yang sudah mempercayai layanan kami!
            </p>
        </div>
        
        <div class="notification-actions">
            <a href="#popular" class="notification-btn notification-btn-primary" onclick="closeWelcomeNotification()">
                🎮 Mulai Top Up Sekarang
            </a>
            <button class="notification-btn notification-btn-secondary" onclick="closeWelcomeNotification()">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- Hero Section -->
<section class="hero-section">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4" style="color: var(--color-text-primary);">
                Top Up Game Instant & Murah
            </h1>
            <p class="text-lg mb-8 max-w-2xl mx-auto" style="color: var(--color-text-muted);">
                Dapatkan diamond, UC, dan item game favorit kamu dengan harga terbaik dan proses yang cepat!
            </p>
            
            <!-- Quick Game Search -->
            <div class="max-w-md mx-auto mb-8">
                <div class="relative">
                    <input type="text" 
                           id="gameSearch" 
                           placeholder="Cari game favorit kamu..." 
                           class="w-full px-6 py-4 pr-12 rounded-2xl text-lg font-medium border-2 transition-all duration-300 focus:outline-none"
                           style="background: var(--color-elevated); color: var(--color-text-primary); border-color: var(--color-border);">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-4">
                        <svg class="w-6 h-6" style="color: var(--color-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
                
                <!-- Search Results Dropdown -->
                <div id="searchResults" class="hidden absolute z-50 w-full mt-2 rounded-xl border shadow-2xl" 
                     style="background: var(--color-elevated); border-color: var(--color-border); box-shadow: var(--shadow-hover);">
                    <div class="py-2 max-h-80 overflow-y-auto">
                        <!-- Results will be populated here -->
                    </div>
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                @auth
                <a href="#popular" class="inline-flex items-center px-8 py-3 rounded-xl font-semibold transition-all duration-300"
                   style="background: var(--color-primary); color: var(--color-base);">
                    🎮 Mulai Top Up
                </a>
                @else
                <a href="{{ route('register') }}" class="inline-flex items-center px-8 py-3 rounded-xl font-semibold transition-all duration-300"
                   style="background: var(--color-primary); color: var(--color-base);">
                    🆓 Daftar Gratis
                </a>
                <a href="{{ route('login') }}" 
                   class="inline-flex items-center px-8 py-3 rounded-xl font-semibold border transition-all duration-300"
                   style="color: var(--color-text-secondary); border-color: var(--color-border);">
                    🔐 Login
                </a>
                @endauth
            </div>
            
            <!-- Popular Payment Methods -->
            <div class="mt-8">
                <p class="text-sm mb-4" style="color: var(--color-text-muted);">Metode pembayaran populer:</p>
                <div class="flex flex-wrap justify-center gap-3">
                    <div class="payment-method-badge">
                        <span class="payment-icon">💳</span>
                        <span>QRIS</span>
                    </div>
                    <div class="payment-method-badge">
                        <span class="payment-icon">🏪</span>
                        <span>Indomaret</span>
                    </div>
                    <div class="payment-method-badge">
                        <span class="payment-icon">🏪</span>
                        <span>Alfamart</span>
                    </div>
                    <div class="payment-method-badge">
                        <span class="payment-icon">💰</span>
                        <span>GoPay</span>
                    </div>
                    <div class="payment-method-badge">
                        <span class="payment-icon">💸</span>
                        <span>OVO</span>
                    </div>
                    <div class="payment-method-badge">
                        <span class="payment-icon">🏦</span>
                        <span>Bank Transfer</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Banner Slider Section -->
@if($banners->count() > 0)
<section class="py-8" style="background: var(--color-base);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="banner-slider" id="bannerSlider">
            @foreach($banners as $index => $banner)
            <div class="banner-slide {{ $index === 0 ? 'active' : '' }}" 
                 onclick="handleBannerClick('{{ $banner->link }}')">
                <img src="{{ asset($banner->image_path) }}" alt="{{ $banner->title }}">
                @if($banner->title)
                <div class="banner-overlay">
                    <div class="banner-title">{{ $banner->title }}</div>
                </div>
                @endif
            </div>
            @endforeach
            
            @if($banners->count() > 1)
            <!-- Navigation Arrows -->
            <button class="banner-nav prev" onclick="previousBanner()">‹</button>
            <button class="banner-nav next" onclick="nextBanner()">›</button>
            
            <!-- Indicators -->
            <div class="banner-indicators">
                @foreach($banners as $index => $banner)
                <button class="banner-indicator {{ $index === 0 ? 'active' : '' }}" 
                        onclick="goToBanner({{ $index }})"></button>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</section>
@endif

<!-- Popular Games Section -->
<section id="popular" class="py-16" style="background: var(--color-surface);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="section-header">
            <span style="font-size: 1.5rem;">🔥</span>
            <div>
                <h2 class="section-title">POPULER SEKARANG</h2>
                <p class="section-subtitle">Game paling diminati berdasarkan transaksi terbanyak</p>
            </div>
        </div>

        <!-- Game Title Popup -->
        <div id="gameTitlePopup" class="game-title-popup">
            <span id="popupGameName"></span>
        </div>

        <!-- Game Cards Grid -->
        <div class="game-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            @forelse($games->take(6) as $index => $game)
            <div class="game-card {{ $index === 0 ? 'active' : '' }}" 
                 onclick="showGameTitlePopup(this, '{{ $game->name }}', '{{ route('games.show', $game->slug) }}')"
                 data-game-name="{{ $game->name }}">
                @if($game->cover_path && file_exists(public_path($game->cover_path)))
                    <img src="{{ asset($game->cover_path) }}" alt="{{ $game->name }}">
                @else
                    <div style="aspect-ratio: 16/9; background: linear-gradient(135deg, var(--color-elevated), var(--color-surface)); display: flex; align-items: center; justify-content: center;">
                        <span style="font-size: 2rem;">🎮</span>
                    </div>
                @endif
                @if($game->is_hot)
                    <div class="hot-badge">HOT</div>
                @endif
                @if($game->orders_count > 0)
                    <div class="popularity-badge">{{ number_format($game->orders_count) }} transaksi</div>
                @endif
                <div class="game-label">
                    <span>{{ $game->name }}</span>
                    <span class="game-vendor">{{ $game->publisher ?? 'Moonton' }}</span>
                </div>
            </div>
            @empty
            <div class="col-span-3 text-center py-12">
                <span style="font-size: 3rem;">🎮</span>
                <h3 class="text-lg font-semibold mt-4" style="color: var(--color-text-primary);">Segera Hadir</h3>
                <p style="color: var(--color-text-muted);">Game favorit akan tersedia segera</p>
            </div>
            @endforelse
        </div>

        <!-- Category Chips -->
        <div class="flex flex-wrap gap-3 mb-8">
            @foreach(['Top Up Games', 'Specialist MLBB', 'Specialist PUBGM', 'Specialist HOK', 'Voucher', 'Pulsa, Data & Tagihan', 'Entertainment'] as $index => $category)
            <button class="chip {{ $index === 0 ? 'active' : '' }}" onclick="selectCategory(this)">
                {{ $category }}
            </button>
            @endforeach
        </div>

        <!-- Popular Games Poster Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @forelse($games->take(12) as $game)
            <div class="poster-card group relative" 
                 onclick="showGameTitlePopup(this, '{{ $game->name }}', '{{ route('games.show', $game->slug) }}')"
                 data-game-name="{{ $game->name }}">
                @if($game->cover_path && file_exists(public_path($game->cover_path)))
                    <img src="{{ asset($game->cover_path) }}" alt="{{ $game->name }}">
                @else
                    <div style="aspect-ratio: 3/4; background: linear-gradient(135deg, var(--color-elevated), var(--color-surface)); display: flex; align-items: center; justify-content: center; flex-direction: column;">
                        <span style="font-size: 2rem;">🎮</span>
                        <span style="font-size: 0.75rem; margin-top: 0.5rem; color: var(--color-text-muted);">{{ $game->name }}</span>
                    </div>
                @endif
                
                @auth
                <!-- Wishlist Button -->
                <button onclick="event.stopPropagation(); console.log('Button clicked!'); toggleWishlist({{ $game->id }})" 
                        id="wishlist-btn-{{ $game->id }}"
                        class="absolute top-2 right-2 w-8 h-8 bg-black bg-opacity-50 hover:bg-opacity-70 rounded-full flex items-center justify-center opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-all z-10"
                        title="Tambah ke wishlist">
                    @if($game->wishlistedByUsers->count() > 0)
                        <i data-lucide="heart" class="w-4 h-4 text-red-500 fill-current"></i>
                    @else
                        <i data-lucide="heart" class="w-4 h-4 text-white"></i>
                    @endif
                </button>
                @endauth
                
                @if($game->is_hot)
                    <div class="hot-badge">HOT</div>
                @endif
                <div class="poster-overlay">
                    <div style="font-weight: 600; font-size: 0.875rem;">{{ $game->name }}</div>
                    <div style="font-size: 0.75rem; opacity: 0.8; margin-top: 0.25rem;">⭐ Top Up</div>
                </div>
            </div>
            @empty
            @for($i = 0; $i < 6; $i++)
            <div class="poster-card">
                <div style="aspect-ratio: 3/4; background: linear-gradient(135deg, var(--color-elevated), var(--color-surface)); display: flex; align-items: center; justify-content: center;">
                    <span style="font-size: 2rem;">🎮</span>
                </div>
                <div class="poster-overlay">
                    <div style="font-weight: 600; font-size: 0.875rem;">Game {{ $i + 1 }}</div>
                    <div style="font-size: 0.75rem; opacity: 0.8; margin-top: 0.25rem;">⭐ Top Up</div>
                </div>
            </div>
            @endfor
            @endforelse
        </div>
    </div>
</section>

<!-- Articles Section -->
<section class="py-16" style="background: var(--color-base);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="section-header">
            <span style="font-size: 1.5rem;">📰</span>
            <div>
                <h2 class="section-title">ARTIKEL TERBARU & BERITA GAME</h2>
                <p class="section-subtitle">Update terbaru dari dunia gaming</p>
            </div>
        </div>

        <div class="article-grid grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            @forelse($articles->take(3) as $article)
            <a href="{{ route('articles.show', $article->slug) }}" class="article-card">
                <div class="article-image">
                    @if($article->featured_image)
                        <img src="{{ asset($article->featured_image) }}" alt="{{ $article->title }}">
                    @else
                        <div style="aspect-ratio: 16/9; background: linear-gradient(135deg, var(--color-elevated), var(--color-surface)); display: flex; align-items: center; justify-content: center;">
                            <span style="font-size: 2rem;">📰</span>
                        </div>
                    @endif
                    <div class="article-overlay">
                        <h3 class="article-title">{{ $article->title }}</h3>
                        <div class="article-meta">
                            <span>{{ $article->author ?? 'Admin' }}</span> • 
                            <span>{{ $article->published_at ? $article->published_at->format('d M Y') : now()->format('d M Y') }}</span>
                        </div>
                    </div>
                </div>
            </a>
            @empty
            @for($i = 0; $i < 3; $i++)
            <div class="article-card">
                <div class="article-image">
                    <div style="aspect-ratio: 16/9; background: linear-gradient(135deg, var(--color-elevated), var(--color-surface)); display: flex; align-items: center; justify-content: center;">
                        <span style="font-size: 2rem;">📰</span>
                    </div>
                    <div class="article-overlay">
                        <h3 class="article-title">Tips & Trick Top Up Game Murah</h3>
                        <div class="article-meta">
                            <span>Admin</span> • <span>{{ now()->format('d M Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endfor
            @endforelse
        </div>

        <div class="text-center">
            <a href="{{ route('articles.index') }}" 
               class="inline-flex items-center px-6 py-2 border rounded-lg font-medium transition-colors"
               style="color: var(--color-text-secondary); border-color: var(--color-border);">
                Lihat Semua Artikel
                <span style="margin-left: 0.5rem;">→</span>
            </a>
        </div>
    </div>
</section>

<script>
// Game Title Popup Animation Function
function showGameTitlePopup(card, gameName, url) {
    // Add clicked animation to card
    card.classList.add('clicked');
    setTimeout(() => {
        card.classList.remove('clicked');
    }, 600);
    
    // Create floating particles from card
    createFloatingParticles(card);
    
    // Show popup with game name
    const popup = document.getElementById('gameTitlePopup');
    const popupText = document.getElementById('popupGameName');
    
    // Set game name with emoji
    const gameEmoji = getGameEmoji(gameName);
    popupText.innerHTML = `${gameEmoji} ${gameName}`;
    
    // Show popup with animation
    popup.classList.add('show');
    
    // Add haptic feedback
    if (navigator.vibrate) {
        navigator.vibrate([100, 50, 100]); // Double vibrate for popup
    }
    
    // Hide popup and navigate after delay
    setTimeout(() => {
        popup.classList.remove('show');
        
        // Navigate to game page
        setTimeout(() => {
            if (url && url !== '#') {
                window.location.href = url;
            }
        }, 200);
    }, 1500); // Show popup for 1.5 seconds
}

// Create floating particles animation
function createFloatingParticles(card) {
    const rect = card.getBoundingClientRect();
    const centerX = rect.left + rect.width / 2;
    const centerY = rect.top + rect.height / 2;
    
    // Create multiple particles
    for (let i = 0; i < 8; i++) {
        setTimeout(() => {
            const particle = document.createElement('div');
            particle.className = 'popup-particle';
            
            // Random position around the card
            const angle = (i / 8) * 2 * Math.PI;
            const radius = 30 + Math.random() * 20;
            const x = centerX + Math.cos(angle) * radius;
            const y = centerY + Math.sin(angle) * radius;
            
            particle.style.left = x + 'px';
            particle.style.top = y + 'px';
            
            document.body.appendChild(particle);
            
            // Remove particle after animation
            setTimeout(() => {
                if (document.body.contains(particle)) {
                    document.body.removeChild(particle);
                }
            }, 1500);
        }, i * 50); // Stagger particle creation
    }
}

// Get appropriate emoji for game
function getGameEmoji(gameName) {
    const name = gameName.toLowerCase();
    if (name.includes('mobile legends')) return '⚔️';
    if (name.includes('pubg')) return '🔫';
    if (name.includes('free fire')) return '🔥';
    if (name.includes('genshin')) return '⚡';
    if (name.includes('valorant')) return '🎯';
    if (name.includes('honor')) return '👑';
    if (name.includes('cod') || name.includes('call of duty')) return '💣';
    if (name.includes('clash')) return '💎';
    if (name.includes('arena')) return '🏟️';
    return '🎮'; // Default game emoji
}

// Legacy function for backward compatibility
function selectGameCard(card, url) {
    const gameName = card.getAttribute('data-game-name') || 'Game';
    showGameTitlePopup(card, gameName, url);
}

// Enhanced Category Chip Selection
function selectCategory(chip) {
    // Check if chip is already active
    if (chip.classList.contains('active')) {
        return;
    }
    
    // Add pulse animation to clicked chip
    chip.style.transform = 'scale(1.05)';
    setTimeout(() => {
        chip.style.transform = '';
    }, 150);
    
    // Remove active class from all chips with animation
    document.querySelectorAll('.chip.active').forEach((c, index) => {
        setTimeout(() => {
            c.classList.remove('active');
        }, index * 30);
    });
    
    // Add active class to clicked chip with delay
    setTimeout(() => {
        chip.classList.add('active');
        
        // Light haptic feedback
        if (navigator.vibrate) {
            navigator.vibrate(30);
        }
        
        // Filter games based on category (if needed)
        filterGamesByCategory(chip.textContent);
    }, 80);
}

// Game filtering by category
function filterGamesByCategory(category) {
    const gameCards = document.querySelectorAll('.game-card');
    
    // If "Semua" is selected, show all games
    if (category === 'Semua') {
        gameCards.forEach((card, index) => {
            setTimeout(() => {
                card.style.display = 'block';
                card.style.opacity = '1';
            }, index * 50);
        });
        return;
    }
    
    // Filter games based on category
    gameCards.forEach((card, index) => {
        const gameCategory = card.dataset.category || 'moba'; // Default category
        const shouldShow = gameCategory.toLowerCase() === category.toLowerCase() || 
                          category === 'Semua';
        
        setTimeout(() => {
            if (shouldShow) {
                // Show the card with nice animation
                card.style.display = 'block';
                card.style.opacity = '1';
                card.style.transform = 'scale(1)';
                card.style.visibility = 'visible';
                card.style.pointerEvents = 'auto';
            } else {
                // Hide with smooth animation
                card.style.opacity = '0';
                card.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    card.style.display = 'none';
                }, 200);
            }
        }, index * 30);
    });
}

// Welcome Notification Functions
function showWelcomeNotification() {
    const notification = document.getElementById('welcomeNotification');
    if (notification) {
        // Show notification with delay
        setTimeout(() => {
            notification.classList.add('show');
            
            // Add entrance animation sound effect (if needed)
            if (navigator.vibrate) {
                navigator.vibrate([200, 100, 200]); // Triple vibrate for welcome
            }
        }, 1000); // Show after 1 second of page load
    }
}

function closeWelcomeNotification() {
    const notification = document.getElementById('welcomeNotification');
    if (notification) {
        notification.classList.remove('show');
        
        // Store in localStorage so it doesn't show again in this session
        localStorage.setItem('welcomeNotificationShown', 'true');
        localStorage.setItem('welcomeNotificationDate', new Date().toDateString());
        
        // Add closing sound effect
        if (navigator.vibrate) {
            navigator.vibrate(50); // Light vibrate on close
        }
    }
}

// Close notification when clicking outside
function handleNotificationBackdropClick(event) {
    if (event.target.id === 'welcomeNotification') {
        closeWelcomeNotification();
    }
}

// Check if notification should be shown
function shouldShowWelcomeNotification() {
    const lastShown = localStorage.getItem('welcomeNotificationDate');
    const today = new Date().toDateString();
    
    // Show notification if:
    // 1. Never shown before, OR
    // 2. Last shown on a different day (daily popup)
    return !lastShown || lastShown !== today;
}

// Enhanced initialization with improved UX
document.addEventListener('DOMContentLoaded', function() {
    // Initialize banner slider
    initBannerSlider();
    
    // Show welcome notification if appropriate
    if (shouldShowWelcomeNotification()) {
        showWelcomeNotification();
    }
    
    // Add event listeners for notification
    const welcomeNotification = document.getElementById('welcomeNotification');
    if (welcomeNotification) {
        welcomeNotification.addEventListener('click', handleNotificationBackdropClick);
    }
    
    // Close notification with ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const notification = document.getElementById('welcomeNotification');
            if (notification && notification.classList.contains('show')) {
                closeWelcomeNotification();
            }
        }
    });
    
    // Gentle initialization to ensure proper display
    setTimeout(() => {
        console.log('🔧 Ensuring proper element initialization...');
        
        // Ensure default "Semua" category is selected and all games show initially
        const semuaChip = document.querySelector('.chip[data-category="Semua"]');
        if (semuaChip && !document.querySelector('.chip.active')) {
            semuaChip.click(); // This will trigger filterGameCards('Semua')
        }
        
        // Ensure critical interactive elements work
        const interactiveElements = document.querySelectorAll('button[type="submit"], .btn-primary, form input');
        interactiveElements.forEach(element => {
            if (getComputedStyle(element).pointerEvents === 'none') {
                element.style.pointerEvents = 'auto';
            }
        });
    }, 500);

    // Auto-select first game card with animation
    const firstGameCard = document.querySelector('.game-card');
    if (firstGameCard && !document.querySelector('.game-card.active')) {
        setTimeout(() => {
            firstGameCard.classList.add('active');
        }, 300); // Delay to let page settle
    }
    
    // Auto-select first category chip with animation
    const firstChip = document.querySelector('.chip');
    if (firstChip && !document.querySelector('.chip.active')) {
        setTimeout(() => {
            firstChip.classList.add('active');
        }, 500); // Slightly later than game card
    }
    
    // Add keyboard navigation support
    document.addEventListener('keydown', function(e) {
        const activeCard = document.querySelector('.game-card.active');
        const activeChip = document.querySelector('.chip.active');
        
        // Arrow key navigation for game cards
        if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
            e.preventDefault();
            const allCards = Array.from(document.querySelectorAll('.game-card'));
            const currentIndex = allCards.indexOf(activeCard);
            
            let nextIndex;
            if (e.key === 'ArrowRight') {
                nextIndex = (currentIndex + 1) % allCards.length;
            } else {
                nextIndex = currentIndex === 0 ? allCards.length - 1 : currentIndex - 1;
            }
            
            if (allCards[nextIndex]) {
                selectGameCard(allCards[nextIndex]);
            }
        }
        
        // Enter key to navigate to selected game
        if (e.key === 'Enter' && activeCard) {
            const link = activeCard.getAttribute('onclick');
            if (link) {
                const url = link.match(/'([^']+)'/)?.[1];
                if (url && url !== '#') {
                    window.location.href = url;
                }
            }
        }
    });
    
    // Add touch gesture support for mobile
    let touchStartX = 0;
    let touchEndX = 0;
    
    document.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });
    
    document.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipeGesture();
    }, { passive: true });
    
    function handleSwipeGesture() {
        const swipeThreshold = 50;
        const diff = touchStartX - touchEndX;
        
        if (Math.abs(diff) > swipeThreshold) {
            const activeCard = document.querySelector('.game-card.active');
            const allCards = Array.from(document.querySelectorAll('.game-card'));
            const currentIndex = allCards.indexOf(activeCard);
            
            let nextIndex;
            if (diff > 0) { // Swipe left - next card
                nextIndex = (currentIndex + 1) % allCards.length;
            } else { // Swipe right - previous card
                nextIndex = currentIndex === 0 ? allCards.length - 1 : currentIndex - 1;
            }
            
            if (allCards[nextIndex]) {
                selectGameCard(allCards[nextIndex]);
            }
        }
    }
    
    // Game Search Functionality
    let gameSearchTimeout;
    const gameSearch = document.getElementById('gameSearch');
    const searchResults = document.getElementById('searchResults');
    
    if (gameSearch) {
        gameSearch.addEventListener('input', function() {
            clearTimeout(gameSearchTimeout);
            const query = this.value.trim();
            
            if (query.length < 2) {
                hideSearchResults();
                return;
            }
            
            gameSearchTimeout = setTimeout(() => {
                searchGames(query);
            }, 300);
        });
        
        gameSearch.addEventListener('focus', function() {
            if (this.value.trim().length >= 2) {
                searchResults.classList.remove('hidden');
            }
        });
        
        // Hide search results when clicking outside
        document.addEventListener('click', function(e) {
            if (!gameSearch.contains(e.target) && !searchResults.contains(e.target)) {
                hideSearchResults();
            }
        });
    }
    
    function searchGames(query) {
        const games = {!! json_encode($games->map(function($game) {
            return [
                'id' => $game->id,
                'name' => $game->name,
                'slug' => $game->slug,
                'publisher' => $game->publisher ?? '',
                'cover_path' => $game->cover_path ?? '',
                'url' => route('games.show', $game->slug)
            ];
        })->values()) !!};
        
        const filteredGames = games.filter(game => 
            game.name.toLowerCase().includes(query.toLowerCase()) ||
            (game.publisher && game.publisher.toLowerCase().includes(query.toLowerCase()))
        );
        
        displaySearchResults(filteredGames, query);
    }
    
    function displaySearchResults(games, query) {
        const resultsContainer = searchResults.querySelector('.py-2');
        
        if (games.length === 0) {
            resultsContainer.innerHTML = `
                <div class="px-4 py-3 text-center" style="color: var(--color-text-muted);">
                    <div class="mb-2">🔍</div>
                    <div>Tidak ada game yang ditemukan untuk "${query}"</div>
                </div>
            `;
        } else {
            resultsContainer.innerHTML = games.map(game => `
                <div class="search-result-item" onclick="navigateToGame('${game.url}')">
                    <div class="search-result-image">
                        ${game.cover_path 
                            ? `<img src="{{ asset('${game.cover_path}') }}" alt="${game.name}" class="search-result-image">`
                            : `<div class="search-result-image flex items-center justify-center"><span>🎮</span></div>`
                        }
                    </div>
                    <div class="search-result-content">
                        <div class="search-result-name">${game.name}</div>
                        <div class="search-result-publisher">${game.publisher || 'Publisher'}</div>
                    </div>
                </div>
            `).join('');
        }
        
        searchResults.classList.remove('hidden');
    }
    
    function hideSearchResults() {
        searchResults.classList.add('hidden');
    }
    
    function navigateToGame(url) {
        window.location.href = url;
    }

    // Banner Slider Functionality
    let currentBannerIndex = 0;
    let bannerInterval;
    const banners = @json($banners->count());
    
    function initBannerSlider() {
        if (banners > 1) {
            startBannerAutoPlay();
        }
    }
    
    function startBannerAutoPlay() {
        bannerInterval = setInterval(nextBanner, 5000); // Change every 5 seconds
    }
    
    function stopBannerAutoPlay() {
        clearInterval(bannerInterval);
    }
    
    function nextBanner() {
        if (banners > 1) {
            goToBanner((currentBannerIndex + 1) % banners);
        }
    }
    
    function previousBanner() {
        if (banners > 1) {
            goToBanner(currentBannerIndex === 0 ? banners - 1 : currentBannerIndex - 1);
        }
    }
    
    function goToBanner(index) {
        if (index === currentBannerIndex || banners <= 1) return;
        
        // Update slides
        const slides = document.querySelectorAll('.banner-slide');
        const indicators = document.querySelectorAll('.banner-indicator');
        
        // Remove active class from current
        slides[currentBannerIndex]?.classList.remove('active');
        indicators[currentBannerIndex]?.classList.remove('active');
        
        // Add active class to new
        slides[index]?.classList.add('active');
        indicators[index]?.classList.add('active');
        
        currentBannerIndex = index;
        
        // Reset auto-play timer
        stopBannerAutoPlay();
        startBannerAutoPlay();
    }
    
    function handleBannerClick(link) {
        if (link && link !== '#' && link !== '') {
            // Stop auto-play when user clicks
            stopBannerAutoPlay();
            
            // Navigate to link
            if (link.startsWith('http')) {
                window.open(link, '_blank');
            } else {
                window.location.href = link;
            }
        }
    }

    @auth
    // Wishlist functionality
    window.toggleWishlist = async function(gameId) {
        console.log('toggleWishlist called with gameId:', gameId);
        
        try {
            console.log('Making request to:', '{{ route("user.wishlist.toggle") }}');
            const response = await fetch('{{ route("user.wishlist.toggle") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ game_id: gameId })
            });
            
            console.log('Response status:', response.status);
            const data = await response.json();
            console.log('Response data:', data);
            
            if (data.success) {
                const button = document.getElementById('wishlist-btn-' + gameId);
                const icon = button.querySelector('i[data-lucide="heart"]');
                
                console.log('Action:', data.action);
                if (data.action === 'added') {
                    icon.classList.add('text-red-500', 'fill-current');
                    icon.classList.remove('text-white');
                } else {
                    icon.classList.remove('text-red-500', 'fill-current');
                    icon.classList.add('text-white');
                }
                
                // Show toast notification
                showToast(data.message, 'success');
                
                // Recreate icons to ensure proper rendering
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            } else {
                showToast(data.message || 'Terjadi kesalahan', 'error');
            }
        } catch (error) {
            console.error('Error toggling wishlist:', error);
            showToast('Terjadi kesalahan sistem', 'error');
        }
    };
    
    // Simple toast notification
    window.showToast = function(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white z-50 transition-all duration-300 ${
            type === 'success' ? 'bg-green-500' : 
            type === 'error' ? 'bg-red-500' : 'bg-blue-500'
        }`;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    };
    @endauth
});
</script>
@endsection