<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">
    
    <!-- Apple Touch Icon -->
    <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/images/icons/icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/images/icons/icon-192x192.png">
    
    <!-- PWA Meta Tags -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Takapedia">
    <meta name="mobile-web-app-capable" content="yes">
    
    <!-- Theme Colors -->
    <meta name="theme-color" content="#FFEA00">
    <meta name="msapplication-navbutton-color" content="#FFEA00">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    
    <!-- Windows Tile -->
    <meta name="msapplication-TileImage" content="/images/icons/icon-144x144.png">
    <meta name="msapplication-TileColor" content="#0E0E0F")

    <!-- SEO Meta Tags -->
    <meta name="description" content="@yield('description', 'Top up game online termurah dan terpercaya')">
    <meta property="og:title" content="@yield('title', config('app.name'))">
    <meta property="og:description" content="@yield('description', 'Top up game online termurah dan terpercaya')">
    <meta property="og:image" content="{{ asset('images/og-image.jpg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Enhanced Interactive Element Initialization -->
    <script>
        // Global state for tracking initialization
        window.takapediaInit = {
            loaded: false,
            alpineReady: false,
            userLoggedIn: @json(auth()->check()),
            retryCount: 0,
            maxRetries: 5
        };

        // Main initialization function
        function initializeInteractiveElements(force = false) {
            if (window.takapediaInit.loaded && !force) return;
            
            console.log('Initializing interactive elements...', {
                userLoggedIn: window.takapediaInit.userLoggedIn,
                retry: window.takapediaInit.retryCount
            });
            
            // Apply z-index hierarchy
            applyZIndexHierarchy();
            
            // Fix form interactions
            fixFormInteractions();
            
            // Initialize dropdowns and menus
            initializeDropdowns();
            
            // Mobile optimizations
            applyMobileOptimizations();
            
            // Mark as loaded
            window.takapediaInit.loaded = true;
            console.log('Interactive elements initialized successfully');
        }

        function applyZIndexHierarchy() {
            // Apply systematic z-index using CSS custom properties
            const elements = {
                'nav, #main-navbar': 'var(--z-elevated)',
                'form, form *': 'var(--z-form-elements)',
                'input, button, select, textarea, label': 'var(--z-form-elements)',
                '[x-show], .dropdown-menu': 'var(--z-dropdown)',
                '.glass::before, .glass::after': 'var(--z-background)',
                '.fixed': 'var(--z-elevated)'
            };
            
            Object.entries(elements).forEach(([selector, zIndex]) => {
                try {
                    document.querySelectorAll(selector).forEach(el => {
                        el.style.zIndex = zIndex;
                        el.style.position = 'relative';
                    });
                } catch (e) {
                    console.warn('Error applying z-index to:', selector, e);
                }
            });
        }

        function fixFormInteractions() {
            console.log('Applying targeted interaction fixes...');
            
            // SELECTIVE FIXES - Only target problematic elements
            const interactiveSelectors = [
                'input', 'button', 'select', 'textarea', 
                '[type="submit"]', '[type="button"]', 
                '.btn-primary', 'form button'
            ];
            
            interactiveSelectors.forEach(selector => {
                document.querySelectorAll(selector).forEach(element => {
                    // Only fix if element has interaction issues
                    const computed = getComputedStyle(element);
                    
                    // Fix pointer events if blocked
                    if (computed.pointerEvents === 'none') {
                        element.style.pointerEvents = 'auto';
                    }
                    
                    // Fix z-index only for forms
                    if (element.closest('form')) {
                        element.style.zIndex = 'var(--z-form-elements)';
                        element.style.position = 'relative';
                    }
                    
                    // Fix cursor only if missing
                    if (['BUTTON', 'A'].includes(element.tagName) && computed.cursor === 'auto') {
                        element.style.cursor = 'pointer';
                    }
                });
            });
            
            // Fix only problematic game cards (if they're actually hidden)
            document.querySelectorAll('.game-card').forEach(card => {
                const computed = getComputedStyle(card);
                if (computed.display === 'none' && !card.hasAttribute('x-show')) {
                    console.log('Found hidden game card, fixing...', card);
                    card.style.display = 'block';
                    card.style.opacity = '1';
                    card.style.visibility = 'visible';
                }
            });
            
            console.log('Targeted fixes applied');
        }

        function initializeDropdowns() {
            // Ensure Alpine.js dropdowns work properly
            document.querySelectorAll('[x-show]').forEach(dropdown => {
                dropdown.style.zIndex = 'var(--z-dropdown)';
                dropdown.style.position = 'absolute';
            });
            
            // User menu specific fixes
            const userMenus = document.querySelectorAll('.relative [x-show]');
            userMenus.forEach(menu => {
                menu.style.zIndex = 'var(--z-dropdown)';
            });
        }

        function applyMobileOptimizations() {
            if (window.innerWidth <= 768) {
                document.body.style.touchAction = 'manipulation';
                
                // Add touch feedback for interactive elements
                document.querySelectorAll('button, .btn-primary, a[href]').forEach(element => {
                    // Remove existing listeners to prevent duplicates
                    element.removeEventListener('touchstart', handleTouchStart);
                    element.removeEventListener('touchend', handleTouchEnd);
                    
                    // Add new listeners
                    element.addEventListener('touchstart', handleTouchStart);
                    element.addEventListener('touchend', handleTouchEnd);
                });
            }
        }

        function handleTouchStart() {
            this.style.opacity = '0.8';
            this.style.transform = 'scale(0.98)';
        }

        function handleTouchEnd() {
            const element = this;
            setTimeout(() => {
                element.style.opacity = '1';
                element.style.transform = 'scale(1)';
            }, 150);
        }

        // Initialize on DOM ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, starting initialization...');
            initializeInteractiveElements();
            
            // Start visibility watcher
            startVisibilityWatcher();
            
            // Re-initialize after Alpine.js loads
            setTimeout(() => {
                if (typeof Alpine !== 'undefined' || window.Alpine) {
                    window.takapediaInit.alpineReady = true;
                    console.log('Alpine.js detected, re-initializing...');
                    initializeInteractiveElements(true);
                }
            }, 500);
            
            // Less frequent re-initialization only if needed
            setInterval(() => {
                // Only re-initialize if there are actual problems detected
                const problemElements = document.querySelectorAll('.game-card[style*="display: none"], button[style*="pointer-events: none"]');
                if (problemElements.length > 0) {
                    console.log('Problems detected, re-initializing...', problemElements.length);
                    initializeInteractiveElements(true);
                }
            }, 10000); // Every 10 seconds and only if needed
        });

        // Re-initialize on window load (fallback)
        window.addEventListener('load', function() {
            console.log('Window loaded, ensuring initialization...');
            initializeInteractiveElements(true);
        });

        // Re-initialize after route changes (for SPA-like behavior)
        document.addEventListener('alpine:init', function() {
            console.log('Alpine initialized, fixing interactions...');
            window.takapediaInit.alpineReady = true;
            setTimeout(() => initializeInteractiveElements(true), 100);
        });

        // Mutation observer to handle dynamic content
        if (typeof MutationObserver !== 'undefined') {
            const observer = new MutationObserver(function(mutations) {
                let shouldReinitialize = false;
                
                mutations.forEach(mutation => {
                    // Check for added nodes that might need initialization
                    if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                        mutation.addedNodes.forEach(node => {
                            if (node.nodeType === 1) { // Element node
                                const hasInteractive = node.querySelector && (
                                    node.querySelector('form') ||
                                    node.querySelector('button') ||
                                    node.querySelector('input') ||
                                    node.querySelector('[x-show]')
                                );
                                
                                if (hasInteractive || ['FORM', 'BUTTON', 'INPUT'].includes(node.tagName)) {
                                    shouldReinitialize = true;
                                }
                            }
                        });
                    }
                });
                
                if (shouldReinitialize) {
                    console.log('Dynamic content detected, re-initializing...');
                    setTimeout(() => initializeInteractiveElements(true), 100);
                }
            });
            
            // Start observing
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }

        // Minimal visibility check for critical elements only
        function startVisibilityWatcher() {
            setInterval(() => {
                // Only check for accidentally hidden critical elements
                const criticalSelectors = ['.game-card', 'button[type="submit"]', 'form input'];
                
                let hiddenCount = 0;
                criticalSelectors.forEach(selector => {
                    document.querySelectorAll(selector).forEach(element => {
                        const computed = getComputedStyle(element);
                        
                        // Only fix if element is unexpectedly hidden (not intentionally)
                        if (computed.display === 'none' && 
                            !element.hasAttribute('x-show') && 
                            !element.classList.contains('hidden') &&
                            !element.hasAttribute('style') || 
                            (element.hasAttribute('style') && !element.style.display)) {
                            
                            console.log('Found accidentally hidden element, fixing:', selector);
                            element.style.display = element.tagName === 'BUTTON' ? 'inline-block' : 'block';
                            element.style.visibility = 'visible';
                            element.style.opacity = '1';
                            hiddenCount++;
                        }
                    });
                });
                
                if (hiddenCount > 0) {
                    console.log('Fixed', hiddenCount, 'accidentally hidden elements');
                }
            }, 5000); // Check every 5 seconds (less frequent)
        }

        // Global function to force re-initialization (for debugging)
        window.reinitializeTakapedia = function() {
            console.log('Force re-initializing...');
            window.takapediaInit.loaded = false;
            initializeInteractiveElements(true);
        };
        
        // Global function to fix only problematic elements
        window.fixVisibilityIssues = function() {
            console.log('Fixing only problematic elements...');
            
            // Fix accidentally hidden game cards
            document.querySelectorAll('.game-card').forEach(card => {
                if (getComputedStyle(card).display === 'none' && !card.hasAttribute('x-show')) {
                    card.style.display = 'block';
                    card.style.opacity = '1';
                    card.style.visibility = 'visible';
                    console.log('Fixed hidden game card:', card);
                }
            });
            
            // Fix blocked interactive elements
            document.querySelectorAll('button, input, form').forEach(element => {
                if (getComputedStyle(element).pointerEvents === 'none') {
                    element.style.pointerEvents = 'auto';
                    console.log('Fixed blocked interaction:', element);
                }
            });
            
            // Ensure "Semua" category is selected by default
            const semuaChip = document.querySelector('.chip[data-category="Semua"]');
            if (semuaChip && !semuaChip.classList.contains('active')) {
                semuaChip.click();
                console.log('Activated "Semua" category filter');
            }
        };
    </script>

    <!-- GSAP -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.4/gsap.min.js"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- reCAPTCHA v3 -->
    @if(config('services.recaptcha.site_key'))
        <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
    @endif

    <style>
        /* ========== Z-INDEX HIERARCHY SYSTEM ========== */
        :root {
            --z-background: -1;
            --z-base: 0;
            --z-content: 10;
            --z-elevated: 100;
            --z-overlay: 500;
            --z-modal: 1000;
            --z-notification: 5000;
            --z-tooltip: 8000;
            --z-dropdown: 9000;
            --z-popover: 9500;
            --z-form-elements: 9999;
            --z-critical: 10000;
        }
        
        /* Background Elements */
        .parallax-bg, .parallax-layer, .parallax-shapes { z-index: var(--z-background); }
        
        /* Base Content */
        .game-card, .denomination-card, .glass, .step-card { z-index: var(--z-content); position: relative; }
        
        /* Navigation & Fixed Elements */
        nav, #main-navbar { z-index: var(--z-elevated) !important; }
        .fixed { z-index: var(--z-elevated); }
        
        /* Overlays & Backdrops */
        .glass::before, .glass::after { z-index: var(--z-background); }
        
        /* Interactive Dropdowns */
        [x-show], .dropdown-menu, .user-menu { z-index: var(--z-dropdown) !important; }
        
        /* Form Elements - Highest Priority */
        form, form *, 
        input, button, select, textarea, label,
        [type="text"], [type="email"], [type="tel"], [type="password"],
        [type="submit"], [type="button"], [type="reset"],
        .btn-primary, form button {
            z-index: var(--z-form-elements) !important;
            position: relative !important;
        }
        
        /* Critical Interactive Elements */
        .mobile-menu button, .scroll-to-top, .customer-service {
            z-index: var(--z-critical) !important;
        }
        
        /* Prevent FOUC for Alpine */
        [x-cloak] { display: none; }
        
        /* SELECTIVE: Fix only specific visibility issues */
        
        /* Ensure game cards are never accidentally hidden */
        .game-card {
            min-height: 200px;
            position: relative;
        }
        
        /* Ensure denomination cards stay visible */
        .denomination-card, .denomination-inner {
            position: relative;
        }
        
        /* Fix form interaction issues only */
        form input, form button, form select, form textarea {
            pointer-events: auto !important;
        }
        
        /* Ensure navigation stays functional */
        nav, #main-navbar {
            position: relative;
        }
        
        /* Only fix Alpine.js cloak */
        [x-cloak] { display: none !important; }
        
        /* Fix only critical interactive elements */
        button:not([x-show]), 
        input:not([x-show]), 
        .btn-primary:not([x-show]) {
            visibility: visible;
            pointer-events: auto;
        }

        /* Simple card background */
        .glass {
            background: rgba(30, 30, 40, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Neon Glow */
        .neon-glow {
            box-shadow:
                0 0 20px rgba(255, 235, 59, 0.5),
                0 0 40px rgba(255, 235, 59, 0.3),
                0 0 60px rgba(255, 235, 59, 0.1);
        }

        .neon-text {
            text-shadow:
                0 0 10px rgba(255, 235, 59, 0.8),
                0 0 20px rgba(255, 235, 59, 0.6),
                0 0 30px rgba(255, 235, 59, 0.4);
        }

        /* Hover Effects */
        .hover-glow { transition: all 0.3s ease; }
        .hover-glow:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(255, 235, 59, 0.3);
        }

        /* Takapedia Dark Theme */
        body {
            background: #0E0E0F;
            min-height: 100vh;
            color: #E6E6E6;
        }
        
        /* Game Cards & Denominations Visibility Enhancement */
        .game-card, .denomination-card {
            display: block;
            visibility: visible;
            opacity: 1;
            transition: all 0.3s ease;
        }
        
        /* Ensure icons load properly */
        .game-card [data-lucide], [data-lucide] {
            display: inline-block;
        }
        
        /* Loading state */
        .games-loading .game-card, .denominations-loading .denomination-card {
            opacity: 0.5;
            transform: scale(0.95);
        }
        
        .games-loaded .game-card, .denominations-loaded .denomination-card {
            opacity: 1;
            transform: scale(1);
        }
        
        /* Denomination specific styling - Prevent GSAP interference */
        .denomination-card label {
            display: block !important;
        }
        
        .denomination-card .glass,
        .denomination-inner,
        #denominations-grid .glass {
            opacity: 1 !important;
            transform: translateY(0) !important;
        }
        
        /* Override any animation that might hide denominations */
        .denomination-card {
            animation: none !important;
        }
        
        /* ========== ENHANCED ANIMATIONS & EFFECTS ========== */
        
        /* Parallax Effect */
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
            animation: parallax-flow 12s ease-in-out infinite;
            transform: translateZ(0);
        }
        
        .parallax-layer {
            position: absolute;
            width: 100%;
            height: 100%;
            will-change: transform;
        }
        
        .parallax-shapes {
            position: absolute;
            opacity: 0.7;
        }
        
        .parallax-shape-1 {
            width: 200px;
            height: 200px;
            background: linear-gradient(45deg, rgba(255, 235, 59, 0.2), rgba(255, 193, 7, 0.1));
            border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
            animation: morph 8s ease-in-out infinite, float-slow 6s ease-in-out infinite;
            top: 10%;
            left: 10%;
        }
        
        .parallax-shape-2 {
            width: 150px;
            height: 150px;
            background: linear-gradient(135deg, rgba(139, 69, 195, 0.2), rgba(168, 85, 247, 0.1));
            border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%;
            animation: morph 10s ease-in-out infinite reverse, float-slow 8s ease-in-out infinite;
            top: 60%;
            right: 15%;
        }
        
        .parallax-shape-3 {
            width: 120px;
            height: 120px;
            background: linear-gradient(225deg, rgba(59, 130, 246, 0.2), rgba(37, 99, 235, 0.1));
            border-radius: 40% 60% 60% 40% / 70% 30% 70% 30%;
            animation: morph 12s ease-in-out infinite, float-slow 10s ease-in-out infinite;
            bottom: 20%;
            left: 60%;
        }
        
        @keyframes parallax-flow {
            0%, 100% { background-position: 0% 50%; transform: rotate(0deg) scale(1); }
            25% { background-position: 100% 25%; transform: rotate(1deg) scale(1.02); }
            50% { background-position: 100% 75%; transform: rotate(0deg) scale(1); }
            75% { background-position: 0% 75%; transform: rotate(-1deg) scale(1.01); }
        }
        
        @keyframes morph {
            0%, 100% { border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%; }
            25% { border-radius: 58% 42% 75% 25% / 76% 46% 54% 24%; }
            50% { border-radius: 50% 50% 33% 67% / 55% 27% 73% 45%; }
            75% { border-radius: 33% 67% 58% 42% / 63% 68% 32% 37%; }
        }
        
        @keyframes float-slow {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }
        
        /* Floating Animation */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        @keyframes float-delayed {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
        }
        
        /* Pulse Animation */
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 20px rgba(255, 235, 59, 0.3); }
            50% { box-shadow: 0 0 30px rgba(255, 235, 59, 0.6), 0 0 40px rgba(255, 235, 59, 0.4); }
        }
        
        /* Gradient Animation */
        @keyframes gradient-shift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* Rotate Animation */
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        /* Scale Bounce */
        @keyframes scale-bounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        /* Accessibility: Focus rings */
        .game-card:focus,
        .denomination-card:focus,
        .glass:focus,
        .btn-primary:focus,
        button:focus,
        a:focus,
        input:focus,
        select:focus,
        textarea:focus {
            outline: 2px solid #FFEA00;
            outline-offset: 2px;
        }

        /* Accessibility: Reduced motion */
        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
            
            .parallax-bg,
            .parallax-layer,
            .parallax-shapes {
                transform: none !important;
                animation: none !important;
            }
        }

        /* Skip to content link */
        .skip-link {
            position: absolute;
            top: -40px;
            left: 6px;
            background: #FFEA00;
            color: #000;
            padding: 8px;
            text-decoration: none;
            border-radius: 4px;
            z-index: 9999;
        }

        .skip-link:focus {
            top: 6px;
        }

        /* Lazy loading images */
        .lazy-image {
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
            background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .lazy-image::before {
            content: '';
            position: absolute;
            width: 40px;
            height: 40px;
            border: 3px solid rgba(255, 234, 0, 0.3);
            border-top: 3px solid #FFEA00;
            border-radius: 50%;
            animation: loading-spin 1s linear infinite;
        }

        .lazy-image.loaded {
            opacity: 1;
            background: none;
            min-height: auto;
        }

        .lazy-image.loaded::before {
            display: none;
        }

        @keyframes loading-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Shimmer Effect */
        @keyframes shimmer {
            0% { background-position: -200px 0; }
            100% { background-position: calc(200px + 100%) 0; }
        }
        
        /* Enhanced Game Cards with Neon Glow */
        .game-card {
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .game-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(255, 235, 59, 0.1) 50%, transparent 70%);
            transform: translateX(-100%);
            transition: transform 0.6s;
            z-index: 1;
        }
        
        .game-card::after {
            content: '';
            position: absolute;
            inset: -2px;
            background: linear-gradient(45deg, 
                rgba(255, 235, 59, 0.8) 0%, 
                rgba(255, 193, 7, 0.6) 25%,
                rgba(245, 158, 11, 0.8) 50%,
                rgba(217, 119, 6, 0.6) 75%,
                rgba(255, 235, 59, 0.8) 100%);
            background-size: 400% 400%;
            border-radius: inherit;
            opacity: 0;
            z-index: -1;
            animation: gradient-border 3s ease infinite;
            transition: opacity 0.3s ease;
        }
        
        @keyframes gradient-border {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        .game-card:hover::before {
            transform: translateX(100%);
        }
        
        .game-card:hover::after {
            opacity: 1;
        }
        
        .game-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 
                0 0 20px rgba(255, 235, 59, 0.6),
                0 0 40px rgba(255, 235, 59, 0.3),
                0 0 60px rgba(255, 235, 59, 0.1),
                0 10px 40px rgba(0, 0, 0, 0.3);
            border-color: rgba(255, 235, 59, 0.5);
        }
        
        .game-card:hover img {
            transform: scale(1.1);
            filter: brightness(1.1) saturate(1.2);
        }
        
        .game-card img {
            transition: all 0.4s ease;
        }
        
        /* Enhanced Denomination Cards */
        .denomination-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .denomination-card:hover {
            transform: translateY(-4px);
        }
        
        .denomination-inner {
            position: relative;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, rgba(30, 30, 40, 0.8) 0%, rgba(50, 50, 60, 0.6) 100%);
        }
        
        .denomination-inner::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, transparent 0%, rgba(255, 235, 59, 0.1) 50%, transparent 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
            border-radius: inherit;
        }
        
        .denomination-card:hover .denomination-inner::after {
            opacity: 1;
        }
        
        /* Floating Elements */
        .hero-floating-1 {
            animation: float 6s ease-in-out infinite;
            animation-delay: 0s;
        }
        
        .hero-floating-2 {
            animation: float-delayed 8s ease-in-out infinite;
            animation-delay: 2s;
        }
        
        .hero-floating-3 {
            animation: float 7s ease-in-out infinite;
            animation-delay: 4s;
        }
        
        /* Enhanced Glassmorphism Effect */
        .glass {
            background: linear-gradient(135deg, 
                rgba(255, 255, 255, 0.1) 0%, 
                rgba(255, 255, 255, 0.05) 50%,
                rgba(255, 255, 255, 0.02) 100%);
            backdrop-filter: blur(20px) saturate(1.8);
            -webkit-backdrop-filter: blur(20px) saturate(1.8);
            border: 1px solid rgba(255, 255, 255, 0.125);
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.15),
                0 0 0 0.5px rgba(255, 255, 255, 0.05);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }
        
        .glass::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, 
                rgba(255, 235, 59, 0.03) 0%, 
                transparent 50%,
                rgba(255, 235, 59, 0.02) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .glass:hover::before {
            opacity: 1;
        }
        
        .glass:hover {
            background: linear-gradient(135deg, 
                rgba(255, 255, 255, 0.18) 0%, 
                rgba(255, 255, 255, 0.08) 50%,
                rgba(255, 255, 255, 0.05) 100%);
            backdrop-filter: blur(25px) saturate(2);
            -webkit-backdrop-filter: blur(25px) saturate(2);
            border-color: rgba(255, 235, 59, 0.2);
            box-shadow: 
                0 16px 64px rgba(0, 0, 0, 0.25),
                inset 0 1px 0 rgba(255, 255, 255, 0.25),
                0 0 0 1px rgba(255, 235, 59, 0.1),
                0 0 20px rgba(255, 235, 59, 0.1);
            transform: translateY(-4px);
        }
        
        /* Enhanced Button Micro Animations */
        .btn-primary {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: #1f2937;
            font-weight: 600;
            padding: 12px 24px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(251, 191, 36, 0.3);
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
        
        .btn-primary::after {
            content: '';
            position: absolute;
            inset: -3px;
            background: linear-gradient(45deg, 
                rgba(255, 235, 59, 0.8), 
                rgba(255, 193, 7, 0.6),
                rgba(245, 158, 11, 0.8));
            background-size: 200% 200%;
            border-radius: inherit;
            opacity: 0;
            z-index: -1;
            animation: gradient-border 2s ease infinite;
            transition: opacity 0.3s ease;
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        .btn-primary:hover::after {
            opacity: 1;
        }
        
        .btn-primary:hover {
            transform: translateY(-4px) scale(1.05);
            box-shadow: 
                0 0 25px rgba(255, 235, 59, 0.6),
                0 0 50px rgba(255, 235, 59, 0.3),
                0 12px 40px rgba(251, 191, 36, 0.4);
            animation: btn-pulse 1.5s ease infinite;
        }
        
        .btn-primary:active {
            transform: translateY(-2px) scale(1.02);
            animation: none;
        }
        
        @keyframes btn-pulse {
            0%, 100% { box-shadow: 0 0 25px rgba(255, 235, 59, 0.6), 0 0 50px rgba(255, 235, 59, 0.3), 0 12px 40px rgba(251, 191, 36, 0.4); }
            50% { box-shadow: 0 0 35px rgba(255, 235, 59, 0.8), 0 0 70px rgba(255, 235, 59, 0.4), 0 16px 50px rgba(251, 191, 36, 0.5); }
        }
        
        /* Loading Animation */
        .loading-dots {
            display: inline-block;
        }
        
        .loading-dots::after {
            content: '';
            animation: loading-dots 1.5s infinite;
        }
        
        @keyframes loading-dots {
            0%, 20% { content: '.'; }
            40% { content: '..'; }
            60%, 100% { content: '...'; }
        }
        
        /* Scroll Animations - Fixed to not interfere with denominations */
        .fade-in-up {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }
        
        .fade-in-up.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Force visibility for critical elements */
        .denomination-card.fade-in-up,
        #denominations-grid .fade-in-up,
        .denomination-card,
        #denominations-grid .denomination-card {
            opacity: 1 !important;
            transform: translateY(0) !important;
            display: block !important;
            visibility: visible !important;
        }
        
        /* Stats Counter Animation */
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            background: linear-gradient(135deg, #fbbf24, #f59e0b, #d97706);
            background-size: 300% 300%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: gradient-shift 3s ease infinite;
        }
        
        /* Enhanced Navigation */
        nav {
            transition: all 0.3s ease;
        }
        
        nav:hover {
            background: rgba(30, 30, 40, 0.9);
            backdrop-filter: blur(25px);
        }
        
        /* Icon Animations */
        [data-lucide] {
            transition: all 0.3s ease;
        }
        
        .icon-rotate:hover [data-lucide] {
            transform: rotate(360deg);
        }
        
        .icon-bounce:hover [data-lucide] {
            animation: scale-bounce 0.6s ease;
        }
        
        /* Payment Method Enhancements */
        .payment-method {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }
        
        .payment-method::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255, 235, 59, 0.1), rgba(255, 235, 59, 0.05));
            opacity: 0;
            transition: opacity 0.3s ease;
            border-radius: inherit;
        }
        
        .payment-method:hover::before {
            opacity: 1;
        }
        
        .payment-method:hover {
            transform: scale(1.02);
            border-color: rgba(255, 235, 59, 0.5);
        }
        
        /* Step Card Enhancements */
        .step-card {
            position: relative;
            transition: all 0.3s ease;
        }
        
        .step-card:hover {
            transform: translateY(-2px);
        }
        
        .step-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 235, 59, 0.05) 0%, transparent 50%);
            opacity: 0;
            transition: opacity 0.3s ease;
            border-radius: inherit;
            pointer-events: none;
        }
        
        .step-card:hover::before {
            opacity: 1;
        }
        
        /* Enhanced Input Fields - Force visibility */
        input[type="text"], input[type="email"], input[type="tel"], input[type="password"], select {
            transition: all 0.3s ease;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            pointer-events: auto !important;
            z-index: 1 !important;
            position: relative !important;
        }
        
        input[type="text"]:focus, input[type="email"]:focus, input[type="tel"]:focus, input[type="password"]:focus, select:focus {
            border-color: rgba(255, 235, 59, 0.7) !important;
            box-shadow: 0 0 0 3px rgba(255, 235, 59, 0.1) !important;
            outline: none !important;
            pointer-events: auto !important;
        }
        
        /* Interactive Elements - Use CSS Variables */
        .step-card input, .step-card label, .step-card div,
        form input, form button, form label, form select,
        .glass input, .glass button, .glass label, .glass select {
            display: block !important;
            visibility: visible !important;
            pointer-events: auto !important;
            opacity: 1 !important;
            z-index: var(--z-form-elements) !important;
            position: relative !important;
        }
        
        /* Enhanced Radio Buttons */
        input[type="radio"]:checked + .denomination-inner {
            border-color: #fbbf24;
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.1) 0%, rgba(245, 158, 11, 0.05) 100%);
            transform: scale(1.02);
            box-shadow: 0 0 20px rgba(251, 191, 36, 0.3);
        }
        
        /* Enhanced Loading Shimmer Effects */
        .loading-shimmer {
            background: linear-gradient(90deg, 
                rgba(255, 255, 255, 0.1) 0%, 
                rgba(255, 235, 59, 0.2) 25%, 
                rgba(255, 255, 255, 0.3) 50%, 
                rgba(255, 235, 59, 0.2) 75%, 
                rgba(255, 255, 255, 0.1) 100%);
            background-size: 200% 100%;
            animation: shimmer 1.8s infinite;
            position: relative;
            overflow: hidden;
        }
        
        .shimmer-skeleton {
            background: linear-gradient(90deg,
                rgba(30, 30, 40, 0.8) 0%,
                rgba(60, 60, 70, 0.6) 50%,
                rgba(30, 30, 40, 0.8) 100%);
            background-size: 200% 100%;
            animation: shimmer-skeleton 1.5s infinite;
            border-radius: 8px;
        }
        
        .card-loading {
            position: relative;
            overflow: hidden;
        }
        
        .card-loading::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, 
                transparent 0%, 
                rgba(255, 235, 59, 0.3) 50%, 
                transparent 100%);
            animation: loading-sweep 2s infinite;
        }
        
        @keyframes loading-sweep {
            0% { left: -100%; }
            100% { left: 100%; }
        }
        
        @keyframes shimmer-skeleton {
            0% { background-position: -200px 0; }
            100% { background-position: calc(200px + 100%) 0; }
        }
        
        /* Alpine.js x-show override for critical elements - more targeted */
        .checkout-summary[x-show],
        [x-show="selectedDenom"] {
            display: block !important;
        }
        
        /* Force visibility for all form elements */
        #checkoutForm input,
        #checkoutForm label,
        #checkoutForm select,
        #checkoutForm .step-card,
        .checkout-summary,
        button[type="submit"],
        button[type="button"],
        .btn-primary,
        form button {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            pointer-events: auto !important;
            z-index: 10 !important;
            cursor: pointer !important;
        }
        /* Comprehensive Interactive Element Fixes */
        form, form *, form input, form button, form select, form textarea {
            pointer-events: auto !important;
            user-select: auto !important;
            -webkit-user-select: auto !important;
            -moz-user-select: auto !important;
            position: relative !important;
            z-index: var(--z-form-elements) !important;
        }
        
        /* Button Interactivity */
        button, [type="button"], [type="submit"], [type="reset"], .btn-primary {
            cursor: pointer !important;
            pointer-events: auto !important;
            display: inline-block !important;
            visibility: visible !important;
            opacity: 1 !important;
            z-index: var(--z-form-elements) !important;
            position: relative !important;
        }
        
        /* Input Field Interactivity */
        input, select, textarea {
            cursor: text !important;
            pointer-events: auto !important;
            user-select: text !important;
            -webkit-user-select: text !important;
            z-index: var(--z-form-elements) !important;
            position: relative !important;
        }
        
        /* Prevent Overlay Interference */
        .glass::before, .glass::after, [class*="overlay"], [class*="backdrop"] {
            pointer-events: none !important;
            z-index: var(--z-background) !important;
        }
        
        /* Container Fixes */
        .glass, form, .form-container, .step-card {
            pointer-events: auto !important;
            position: relative !important;
            z-index: var(--z-content) !important;
        }
        
        /* Mobile Responsive Enhancements */
        @media (max-width: 768px) {
            .hero-section {
                min-height: 70vh !important;
                padding: 2rem 1rem !important;
            }
            
            .hero-section h1 {
                font-size: 2rem !important;
                line-height: 1.2 !important;
                margin-bottom: 1rem !important;
            }
            
            .hero-section p {
                font-size: 1rem !important;
                margin-bottom: 2rem !important;
            }
            
            .hero-section .flex {
                flex-direction: column !important;
                gap: 0.75rem !important;
            }
            
            .hero-section a {
                padding: 0.875rem 1.5rem !important;
                font-size: 0.875rem !important;
                text-align: center !important;
            }
            
            .glass {
                padding: 1rem !important;
                margin: 0.5rem !important;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 0.75rem !important;
            }
            
            .features-grid {
                grid-template-columns: 1fr !important;
                gap: 1rem !important;
            }
            
            .payment-slider {
                animation-duration: 20s !important;
            }
            
            .payment-method {
                min-width: 100px !important;
                font-size: 0.75rem !important;
                padding: 0.5rem !important;
            }
        }
        
        @media (max-width: 640px) {
            .hero-section h1 {
                font-size: 1.75rem !important;
            }
            
            .stats-grid {
                grid-template-columns: 1fr !important;
                gap: 0.5rem !important;
            }
            
            .glass {
                padding: 0.75rem !important;
                margin: 0.25rem !important;
            }
            
            input, button, select {
                font-size: 16px !important; /* Prevent zoom on iOS */
                padding: 0.75rem !important;
            }
            
            .btn-primary, button[type="submit"] {
                padding: 1rem !important;
                font-size: 1rem !important;
            }
        }
        
        @media (max-width: 480px) {
            .container, .max-w-7xl {
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }
            
            .hero-section {
                min-height: 60vh !important;
                padding: 1.5rem 0.5rem !important;
            }
            
            .hero-section h1 {
                font-size: 1.5rem !important;
            }
            
            .glass {
                padding: 0.5rem !important;
                border-radius: 0.5rem !important;
            }
        }
    </style>

    @stack('styles')
</head>
<body class="font-sans antialiased text-gray-100">
    <!-- Accessibility: Skip to content -->
    <a href="#main-content" class="skip-link">Skip to main content</a>
<div class="min-h-screen">
    <!-- Navigation -->
    <nav id="main-navbar" class="sticky top-0 border-b transition-all duration-300" style="background: #0E0E0F; border-color: rgba(255, 255, 255, 0.08); z-index: var(--z-elevated);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 transition-all duration-300" id="nav-content">
                <div class="flex items-center flex-1">
                    <!-- Logo -->
                    <a href="{{ route('home') }}" class="flex items-center mr-8">
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-lg flex items-center justify-center">
                                <i data-lucide="zap" class="w-5 h-5 text-gray-900"></i>
                            </div>
                            <span class="logo text-2xl font-bold transition-all duration-300" style="color: #FFEA00;">Aratopup</span>
                        </div>
                    </a>

                    <!-- Search Bar (Centered) -->
                    <div class="hidden sm:block flex-1 max-w-md mx-auto">
                        <form action="{{ route('home') }}" method="GET">
                            <div class="relative">
                                <input
                                    type="text"
                                    name="search"
                                    placeholder="Cari Game atau Voucher"
                                    value="{{ request('search') }}"
                                    class="w-full px-4 py-2.5 pl-10 pr-4 rounded-xl border focus:outline-none transition-all duration-300"
                                    style="background: #1F1F2A; border-color: rgba(255, 255, 255, 0.08); color: #E6E6E6; pointer-events: auto !important; z-index: 500 !important; position: relative !important;"
                                    onfocus="this.style.borderColor='#FFEA00'"
                                >
                                <i data-lucide="search" class="absolute left-3 top-3 w-4 h-4 text-gray-400"></i>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Navigation Items -->
                <div class="hidden md:flex items-center space-x-3 xl:space-x-6 mr-6">
                    <a href="{{ route('home') }}" class="nav-link flex items-center space-x-1 hover:text-yellow-400 transition-colors px-2 xl:px-3 py-2 rounded-lg hover:bg-yellow-400/10">
                        <span class="font-medium text-sm xl:text-base">Topup</span>
                    </a>

                    <a href="{{ route('transactions.check') }}" class="nav-link flex items-center space-x-1 hover:text-yellow-400 transition-colors px-2 xl:px-3 py-2 rounded-lg hover:bg-yellow-400/10">
                        <span class="font-medium text-sm xl:text-base">Cek</span>
                    </a>

                    <a href="{{ route('leaderboard') }}" class="nav-link flex items-center space-x-1 hover:text-yellow-400 transition-colors px-2 xl:px-3 py-2 rounded-lg hover:bg-yellow-400/10">
                        <span class="font-medium text-sm xl:text-base">Board</span>
                    </a>

                    <a href="{{ route('articles.index') }}" class="nav-link flex items-center space-x-1 hover:text-yellow-400 transition-colors px-2 xl:px-3 py-2 rounded-lg hover:bg-yellow-400/10">
                        <span class="font-medium text-sm xl:text-base">Artikel</span>
                    </a>

                    <a href="{{ route('calculator') }}" class="nav-link flex items-center space-x-1 hover:text-yellow-400 transition-colors px-2 xl:px-3 py-2 rounded-lg hover:bg-yellow-400/10">
                        <span class="font-medium text-sm xl:text-base">Calc</span>
                    </a>
                </div>

                <!-- Auth Buttons -->
                <div class="flex items-center space-x-3">

                    @auth
                        <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                            <button
                                @click="open = !open"
                                class="flex items-center space-x-2 hover:text-yellow-400 transition px-3 py-2 rounded-lg hover:bg-yellow-400/10"
                                aria-haspopup="true"
                                :aria-expanded="open"
                            >
                                <div class="w-8 h-8 bg-gradient-to-br from-purple-400 to-purple-600 rounded-full flex items-center justify-center">
                                    <i data-lucide="user" class="w-4 h-4 text-white"></i>
                                </div>
                                <span class="font-medium">{{ auth()->user()->name }}</span>
                            </button>

                            <div
                                x-cloak
                                x-show="open"
                                x-transition
                                @click.outside="open = false"
                                class="absolute right-0 mt-2 w-56 glass rounded-lg overflow-hidden"
                            >
                                @if(auth()->user()->is_admin)
                                    <a href="/admin" class="block px-4 py-2 hover:bg-gray-700">
                                        <i data-lucide="shield" class="inline w-4 h-4 mr-2"></i>
                                        Admin Panel
                                    </a>
                                    <div class="border-t border-gray-700"></div>
                                @endif

                                <a href="{{ route('user.dashboard') }}" class="block px-4 py-2 hover:bg-gray-700">
                                    <i data-lucide="layout-dashboard" class="inline w-4 h-4 mr-2"></i>
                                    Dashboard
                                </a>
                                <a href="{{ route('user.orders') }}" class="block px-4 py-2 hover:bg-gray-700">
                                    <i data-lucide="shopping-bag" class="inline w-4 h-4 mr-2"></i>
                                    Order History
                                </a>
                                <a href="{{ route('user.profile.edit') }}" class="block px-4 py-2 hover:bg-gray-700">
                                    <i data-lucide="user-cog" class="inline w-4 h-4 mr-2"></i>
                                    Profile Settings
                                </a>
                                <a href="{{ route('user.promos') }}" class="block px-4 py-2 hover:bg-gray-700">
                                    <i data-lucide="tag" class="inline w-4 h-4 mr-2"></i>
                                    My Promos
                                </a>
                                <a href="{{ route('user.rewards.index') }}" class="block px-4 py-2 hover:bg-gray-700">
                                    <span class="inline w-4 h-4 mr-2">💎</span>
                                    Rewards
                                </a>
                                <a href="{{ route('user.achievements.index') }}" class="block px-4 py-2 hover:bg-gray-700">
                                    <span class="inline w-4 h-4 mr-2">🏆</span>
                                    Achievements
                                </a>
                                <a href="{{ route('user.gaming-profile.index') }}" class="block px-4 py-2 hover:bg-gray-700">
                                    <span class="inline w-4 h-4 mr-2">🎮</span>
                                    Gaming Profile
                                </a>
                                <a href="{{ route('user.reviews') }}" class="block px-4 py-2 hover:bg-gray-700">
                                    <i data-lucide="star" class="inline w-4 h-4 mr-2"></i>
                                    My Reviews
                                </a>
                                <a href="{{ route('user.wishlist.index') }}" class="block px-4 py-2 hover:bg-gray-700">
                                    <i data-lucide="heart" class="inline w-4 h-4 mr-2"></i>
                                    Wishlist
                                </a>
                                <a href="{{ route('user.support') }}" class="block px-4 py-2 hover:bg-gray-700">
                                    <i data-lucide="help-circle" class="inline w-4 h-4 mr-2"></i>
                                    Support
                                </a>

                                <div class="border-t border-gray-700"></div>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 hover:bg-gray-700">
                                        <i data-lucide="log-out" class="inline w-4 h-4 mr-2"></i>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="flex space-x-2">
                            <a href="{{ route('login') }}" class="px-4 py-2 rounded-xl border font-semibold transition-all duration-300"
                               style="border-color: rgba(255, 255, 255, 0.08); color: #E6E6E6;"
                               onmouseover="this.style.borderColor='#FFEA00'; this.style.color='#FFEA00'"
                               onmouseout="this.style.borderColor='rgba(255, 255, 255, 0.08)'; this.style.color='#E6E6E6'">
                                Masuk
                            </a>
                            <a href="{{ route('register') }}" class="px-4 py-2 rounded-xl font-semibold transition-all duration-300"
                               style="background: #FFEA00; color: #0E0E0F;"
                               onmouseover="this.style.background='#FFC700'"
                               onmouseout="this.style.background='#FFEA00'">
                                Daftar
                            </a>
                        </div>
                    @endauth
                    
                </div>
            </div>
            
        </div>
    </nav>

    <!-- Main Content -->
    <main id="main-content">
        @yield('content')
    </main>

    <!-- Footer Takapedia Style -->
    <footer class="relative mt-20" style="background: #000000;">
        <!-- Yellow Lightning Wave Hero Element -->
        <div class="relative overflow-hidden">
            <svg class="absolute inset-x-0 top-0" viewBox="0 0 1200 120" style="height: 60px;">
                <path d="M0,60 Q300,10 600,60 T1200,60 L1200,120 L0,120 Z" fill="#FFEA00" opacity="0.8"/>
                <path d="M0,70 Q300,20 600,70 T1200,70 L1200,120 L0,120 Z" fill="#FFC700" opacity="0.6"/>
            </svg>
            <div class="relative pt-20 pb-16">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <!-- Brand Description -->
                    <div class="text-center mb-12">
                        <div class="flex items-center justify-center space-x-3 mb-4">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: #FFEA00;">
                                <i data-lucide="zap" class="w-6 h-6" style="color: #000000;"></i>
                            </div>
                            <span class="text-3xl font-bold" style="color: #FFEA00;">Aratopup</span>
                        </div>
                        <p class="max-w-2xl mx-auto text-sm leading-relaxed" style="color: #A8A8A8;">
                            Platform top up game terpercaya #1 di Indonesia. Dapatkan diamond, UC, dan item game favorit 
                            dengan harga terbaik, proses instant, dan keamanan terjamin. Melayani jutaan gamer sejak 2020.
                        </p>
                    </div>

                    <!-- Yellow Separator Line -->
                    <div class="w-full h-px mb-12" style="background: #FFEA00;"></div>

                    <!-- 4 Column Links -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-12">
                        <!-- Peta Situs -->
                        <div>
                            <h3 class="font-semibold mb-4" style="color: #FFFFFF;">Peta Situs</h3>
                            <ul class="space-y-3">
                                <li><a href="{{ route('home') }}" class="text-sm transition-colors hover:underline" style="color: #A8A8A8;" onmouseover="this.style.color='#FFEA00'" onmouseout="this.style.color='#A8A8A8'">Beranda</a></li>
                                <li><a href="#popular" class="text-sm transition-colors hover:underline" style="color: #A8A8A8;" onmouseover="this.style.color='#FFEA00'" onmouseout="this.style.color='#A8A8A8'">Game Populer</a></li>
                                <li><a href="{{ route('calculator') }}" class="text-sm transition-colors hover:underline" style="color: #A8A8A8;" onmouseover="this.style.color='#FFEA00'" onmouseout="this.style.color='#A8A8A8'">Kalkulator</a></li>
                                <li><a href="{{ route('leaderboard') }}" class="text-sm transition-colors hover:underline" style="color: #A8A8A8;" onmouseover="this.style.color='#FFEA00'" onmouseout="this.style.color='#A8A8A8'">Leaderboard</a></li>
                                <li><a href="{{ route('articles.index') }}" class="text-sm transition-colors hover:underline" style="color: #A8A8A8;" onmouseover="this.style.color='#FFEA00'" onmouseout="this.style.color='#A8A8A8'">Artikel & News</a></li>
                            </ul>
                        </div>

                        <!-- Dukungan -->
                        <div>
                            <h3 class="font-semibold mb-4" style="color: #FFFFFF;">Dukungan</h3>
                            <ul class="space-y-3">
                                <li><a href="{{ route('transactions.check') }}" class="text-sm transition-colors hover:underline" style="color: #A8A8A8;" onmouseover="this.style.color='#FFEA00'" onmouseout="this.style.color='#A8A8A8'">Cek Transaksi</a></li>
                                <li><a href="#" class="text-sm transition-colors hover:underline" style="color: #A8A8A8;" onmouseover="this.style.color='#FFEA00'" onmouseout="this.style.color='#A8A8A8'">FAQ</a></li>
                                <li><a href="#" class="text-sm transition-colors hover:underline" style="color: #A8A8A8;" onmouseover="this.style.color='#FFEA00'" onmouseout="this.style.color='#A8A8A8'">Live Chat</a></li>
                                <li><a href="#" class="text-sm transition-colors hover:underline" style="color: #A8A8A8;" onmouseover="this.style.color='#FFEA00'" onmouseout="this.style.color='#A8A8A8'">WhatsApp</a></li>
                                <li><a href="#" class="text-sm transition-colors hover:underline" style="color: #A8A8A8;" onmouseover="this.style.color='#FFEA00'" onmouseout="this.style.color='#A8A8A8'">Tutorial</a></li>
                            </ul>
                        </div>

                        <!-- Legalitas -->
                        <div>
                            <h3 class="font-semibold mb-4" style="color: #FFFFFF;">Legalitas</h3>
                            <ul class="space-y-3">
                                <li><a href="#" class="text-sm transition-colors hover:underline" style="color: #A8A8A8;" onmouseover="this.style.color='#FFEA00'" onmouseout="this.style.color='#A8A8A8'">Syarat & Ketentuan</a></li>
                                <li><a href="#" class="text-sm transition-colors hover:underline" style="color: #A8A8A8;" onmouseover="this.style.color='#FFEA00'" onmouseout="this.style.color='#A8A8A8'">Kebijakan Privasi</a></li>
                                <li><a href="#" class="text-sm transition-colors hover:underline" style="color: #A8A8A8;" onmouseover="this.style.color='#FFEA00'" onmouseout="this.style.color='#A8A8A8'">Kebijakan Refund</a></li>
                                <li><a href="#" class="text-sm transition-colors hover:underline" style="color: #A8A8A8;" onmouseover="this.style.color='#FFEA00'" onmouseout="this.style.color='#A8A8A8'">Panduan Keamanan</a></li>
                            </ul>
                        </div>

                        <!-- Social Media -->
                        <div>
                            <h3 class="font-semibold mb-4" style="color: #FFFFFF;">Social Media</h3>
                            <ul class="space-y-3">
                                <li><a href="#" class="flex items-center text-sm transition-colors hover:underline" style="color: #A8A8A8;" onmouseover="this.style.color='#FFEA00'" onmouseout="this.style.color='#A8A8A8'">
                                    <i data-lucide="facebook" class="w-4 h-4 mr-2"></i>Facebook
                                </a></li>
                                <li><a href="#" class="flex items-center text-sm transition-colors hover:underline" style="color: #A8A8A8;" onmouseover="this.style.color='#FFEA00'" onmouseout="this.style.color='#A8A8A8'">
                                    <i data-lucide="instagram" class="w-4 h-4 mr-2"></i>Instagram
                                </a></li>
                                <li><a href="#" class="flex items-center text-sm transition-colors hover:underline" style="color: #A8A8A8;" onmouseover="this.style.color='#FFEA00'" onmouseout="this.style.color='#A8A8A8'">
                                    <i data-lucide="twitter" class="w-4 h-4 mr-2"></i>Twitter
                                </a></li>
                                <li><a href="#" class="flex items-center text-sm transition-colors hover:underline" style="color: #A8A8A8;" onmouseover="this.style.color='#FFEA00'" onmouseout="this.style.color='#A8A8A8'">
                                    <i data-lucide="youtube" class="w-4 h-4 mr-2"></i>YouTube
                                </a></li>
                                <li><a href="#" class="flex items-center text-sm transition-colors hover:underline" style="color: #A8A8A8;" onmouseover="this.style.color='#FFEA00'" onmouseout="this.style.color='#A8A8A8'">
                                    <i data-lucide="message-circle" class="w-4 h-4 mr-2"></i>Discord
                                </a></li>
                            </ul>
                        </div>
                    </div>

                    <!-- Bottom Copyright -->
                    <div class="text-center pt-8 border-t" style="border-color: rgba(255, 255, 255, 0.08);">
                        <p class="text-xs" style="color: #666666;">
                            © {{ date('Y') }} Aratopup. Hak cipta dilindungi undang-undang. Platform top up game terpercaya.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scroll to Top Button -->
        <button id="scrollToTop" 
                class="fixed bottom-6 right-6 w-12 h-12 rounded-full shadow-lg opacity-0 invisible transition-all duration-300"
                style="background: #FFEA00; color: #000000; z-index: var(--z-elevated);"
                onclick="scrollToTop()"
                onmouseover="this.style.background='#FFC700'"
                onmouseout="this.style.background='#FFEA00'">
            <i data-lucide="chevron-up" class="w-6 h-6 mx-auto"></i>
        </button>
    </footer>
</div>

<!-- Customer Service Float Button -->
<div class="fixed bottom-4 right-4" style="z-index: var(--z-elevated);">
    <button class="bg-yellow-400 text-gray-900 p-3 sm:p-4 rounded-full shadow-lg hover:bg-yellow-500 transition"
            style="pointer-events: auto !important; z-index: var(--z-elevated) !important;">
        <i data-lucide="headphones" class="w-5 h-5 sm:w-6 sm:h-6"></i>
    </button>
</div>

<script>

    // Scroll to top functionality
    function scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    // Show/hide scroll to top button
    function handleScrollToTop() {
        const scrollBtn = document.getElementById('scrollToTop');
        if (!scrollBtn) return;

        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                scrollBtn.classList.remove('opacity-0', 'invisible');
                scrollBtn.classList.add('opacity-100', 'visible');
            } else {
                scrollBtn.classList.add('opacity-0', 'invisible');
                scrollBtn.classList.remove('opacity-100', 'visible');
            }
        });
    }

    // Lazy loading images
    function initLazyLoading() {
        const lazyImages = document.querySelectorAll('img[data-src]');
        
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy-image');
                        img.classList.add('loaded');
                        img.removeAttribute('data-src');
                        imageObserver.unobserve(img);
                        
                        img.onload = () => {
                            img.classList.add('loaded');
                        };
                    }
                });
            }, {
                rootMargin: '50px 0px'
            });

            lazyImages.forEach(img => {
                img.classList.add('lazy-image');
                imageObserver.observe(img);
            });
        } else {
            // Fallback for older browsers
            lazyImages.forEach(img => {
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                img.classList.add('loaded');
            });
        }
    }

    // Navbar shrink on scroll
    function handleNavbarShrink() {
        const navbar = document.getElementById('main-navbar');
        const navContent = document.getElementById('nav-content');
        const logo = document.querySelector('.logo');
        
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('navbar-shrink');
                navContent.classList.add('h-12');
                navContent.classList.remove('h-16');
                if (logo) {
                    logo.style.fontSize = '1.5rem';
                }
            } else {
                navbar.classList.remove('navbar-shrink');
                navContent.classList.add('h-16');
                navContent.classList.remove('h-12');
                if (logo) {
                    logo.style.fontSize = '2rem';
                }
            }
        });
    }

    // Ensure all scripts are loaded before initialization
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Lucide icons with retry mechanism
        function initLucideIcons() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
                console.log('Lucide icons initialized');
                return true;
            }
            return false;
        }
        
        // Try to initialize immediately
        if (!initLucideIcons()) {
            // Retry after a short delay
            setTimeout(initLucideIcons, 500);
        }
        
        // GSAP Animations with careful exclusions
        if (typeof gsap !== 'undefined') {
            console.log('GSAP detected, applying safe animations...');
            
            // Only animate safe elements, exclude critical game elements
            const safeElementsToAnimate = ".glass:not(.game-card):not(.denomination-card):not(.denomination-inner):not([class*='game']):not([id*='denominations'])";
            
            try {
                gsap.from(safeElementsToAnimate, {
                    y: -20,
                    opacity: 0,
                    duration: 0.5,
                    stagger: 0.1,
                    onComplete: function() {
                        console.log('GSAP animation completed safely');
                    }
                });
            } catch (e) {
                console.warn('GSAP animation failed:', e);
            }
        }
        
        // Initialize navbar shrink effect
        handleNavbarShrink();

        // Initialize scroll to top button
        handleScrollToTop();

        // Initialize lazy loading
        initLazyLoading();

        // Ensure games and denominations are visible after page load
        setTimeout(function() {
            const gameCards = document.querySelectorAll('.game-card');
            const denominationCards = document.querySelectorAll('.denomination-card');
            const gamesGrid = document.getElementById('games-grid');
            const denominationsGrid = document.getElementById('denominations-grid');
            
            if (gamesGrid) {
                gamesGrid.classList.add('games-loaded');
                gamesGrid.classList.remove('games-loading');
            }
            
            if (denominationsGrid) {
                denominationsGrid.classList.add('denominations-loaded');
                denominationsGrid.classList.remove('denominations-loading');
            }
            
            // Force visibility for any hidden game cards
            gameCards.forEach(function(card) {
                const computedStyle = window.getComputedStyle(card);
                if (computedStyle.display === 'none' && !card.style.display) {
                    card.style.display = 'block';
                }
            });
            
            // Force visibility for any hidden denomination cards
            denominationCards.forEach(function(card) {
                const computedStyle = window.getComputedStyle(card);
                if (computedStyle.display === 'none' && !card.style.display) {
                    card.style.display = 'block';
                    card.style.visibility = 'visible';
                    card.style.opacity = '1';
                }
            });
            
            console.log('Visibility checked. Found', gameCards.length, 'game cards and', denominationCards.length, 'denomination cards');
        }, 100);
    });
    
    // Fallback initialization on window load
    window.addEventListener('load', function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
    
    // PWA Service Worker Registration
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', async () => {
            try {
                const registration = await navigator.serviceWorker.register('/sw.js');
                console.log('ServiceWorker registered: ', registration);
                
                // Listen for updates
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed') {
                            if (navigator.serviceWorker.controller) {
                                showUpdateAvailable();
                            }
                        }
                    });
                });
            } catch (error) {
                console.error('ServiceWorker registration failed: ', error);
            }
        });
    }
    
    // PWA Install Prompt
    let deferredPrompt;
    window.addEventListener('beforeinstallprompt', (e) => {
        console.log('PWA install prompt triggered');
        e.preventDefault();
        deferredPrompt = e;
        showInstallButton();
    });
    
    function showInstallButton() {
        const installButton = document.createElement('button');
        installButton.innerHTML = `
            <i data-lucide="download" class="w-4 h-4 mr-2"></i>
            Install App
        `;
        installButton.className = 'fixed bottom-4 right-4 bg-yellow-400 text-gray-900 px-4 py-2 rounded-lg font-semibold shadow-lg hover:bg-yellow-500 transition z-50 flex items-center';
        installButton.id = 'install-button';
        
        installButton.addEventListener('click', async () => {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                console.log('PWA install outcome:', outcome);
                deferredPrompt = null;
                installButton.remove();
            }
        });
        
        document.body.appendChild(installButton);
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
        
        setTimeout(() => {
            if (document.getElementById('install-button')) {
                installButton.style.opacity = '0.7';
            }
        }, 10000);
    }
    
    function showUpdateAvailable() {
        const updateNotification = document.createElement('div');
        updateNotification.innerHTML = `
            <div class="fixed top-4 left-4 bg-blue-600 text-white px-6 py-3 rounded-lg shadow-lg z-50 max-w-sm">
                <p class="font-semibold mb-2">🚀 Update Available!</p>
                <p class="text-sm mb-3">New version ready. Refresh to update.</p>
                <div class="flex space-x-2">
                    <button onclick="location.reload()" class="bg-white text-blue-600 px-3 py-1 rounded text-sm font-medium">
                        Update Now
                    </button>
                    <button onclick="this.parentElement.parentElement.parentElement.remove()" class="border border-white text-white px-3 py-1 rounded text-sm">
                        Later
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(updateNotification);
    }
    
    // PWA status tracking
    window.addEventListener('appinstalled', () => {
        console.log('PWA was installed');
        if (typeof gtag !== 'undefined') {
            gtag('event', 'pwa_install', {
                event_category: 'PWA',
                event_label: 'App Installed'
            });
        }
    });
</script>

@stack('scripts')
</body>
</html>
