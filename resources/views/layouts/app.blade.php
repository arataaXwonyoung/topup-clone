<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>

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

    <!-- GSAP -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.4/gsap.min.js"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- reCAPTCHA v3 -->
    @if(config('services.recaptcha.site_key'))
        <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
    @endif

    <style>
        /* Prevent FOUC for Alpine */
        [x-cloak] { display: none; }

        /* Glassmorphism */
        .glass {
            background: rgba(30, 30, 40, 0.7);
            backdrop-filter: blur(10px);
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

        /* Dark Theme */
        body {
            background: linear-gradient(135deg, #0f0f14 0%, #1a1a24 100%);
            min-height: 100vh;
        }
    </style>

    @stack('styles')
</head>
<body class="font-sans antialiased text-gray-100">
<div class="min-h-screen">
    <!-- Navigation -->
    <nav class="glass sticky top-0 z-50 border-b border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <!-- Logo -->
                    <a href="{{ route('home') }}" class="flex items-center">
                        <span class="text-2xl font-bold text-yellow-400 neon-text">⚡ Aratopup</span>
                    </a>

                    <!-- Search Bar -->
                    <div class="ml-8 hidden md:block">
                        <form action="{{ route('home') }}" method="GET">
                            <div class="relative">
                                <input
                                    type="text"
                                    name="search"
                                    placeholder="Cari Game atau Voucher"
                                    value="{{ request('search') }}"
                                    class="w-80 px-4 py-2 pl-10 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none"
                                >
                                <i data-lucide="search" class="absolute left-3 top-2.5 w-5 h-5 text-gray-400"></i>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Navigation Items -->
                <div class="flex items-center space-x-6">
                    <a href="{{ route('home') }}" class="flex items-center space-x-2 hover:text-yellow-400 transition">
                        <i data-lucide="home" class="w-5 h-5"></i>
                        <span>Topup</span>
                    </a>

                    <a href="{{ route('transactions.check') }}" class="flex items-center space-x-2 hover:text-yellow-400 transition">
                        <i data-lucide="search-check" class="w-5 h-5"></i>
                        <span>Cek Transaksi</span>
                    </a>

                    <a href="{{ route('leaderboard') }}" class="flex items-center space-x-2 hover:text-yellow-400 transition">
                        <i data-lucide="trophy" class="w-5 h-5"></i>
                        <span>Leaderboard</span>
                    </a>

                    <a href="{{ route('articles.index') }}" class="flex items-center space-x-2 hover:text-yellow-400 transition">
                        <i data-lucide="newspaper" class="w-5 h-5"></i>
                        <span>Artikel</span>
                    </a>

                    <a href="{{ route('calculator') }}" class="flex items-center space-x-2 hover:text-yellow-400 transition">
                        <i data-lucide="calculator" class="w-5 h-5"></i>
                        <span>Kalkulator</span>
                    </a>

                    @auth
                        <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                            <button
                                @click="open = !open"
                                class="flex items-center space-x-2 hover:text-yellow-400 transition"
                                aria-haspopup="true"
                                :aria-expanded="open"
                            >
                                <i data-lucide="user" class="w-5 h-5"></i>
                                <span>{{ auth()->user()->name }}</span>
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
                                <a href="{{ route('user.reviews') }}" class="block px-4 py-2 hover:bg-gray-700">
                                    <i data-lucide="star" class="inline w-4 h-4 mr-2"></i>
                                    My Reviews
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
                            <a href="{{ route('login') }}" class="px-4 py-2 rounded-lg border border-yellow-400 text-yellow-400 hover:bg-yellow-400 hover:text-gray-900 transition">
                                Masuk
                            </a>
                            <a href="{{ route('register') }}" class="px-4 py-2 rounded-lg bg-yellow-400 text-gray-900 hover:bg-yellow-500 transition">
                                Daftar
                            </a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="glass mt-20 border-t border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-lg font-semibold text-yellow-400 mb-4">Takapedia Clone</h3>
                    <p class="text-gray-400">Platform top-up game terpercaya dengan harga termurah dan proses instant.</p>
                </div>

                <div>
                    <h4 class="text-lg font-semibold mb-4">Layanan</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="{{ route('home') }}?category=games" class="hover:text-yellow-400">Top Up Games</a></li>
                        <li><a href="{{ route('home') }}?category=voucher" class="hover:text-yellow-400">Voucher</a></li>
                        <li><a href="{{ route('home') }}?category=pulsa" class="hover:text-yellow-400">Pulsa & Tagihan</a></li>
                        <li><a href="{{ route('home') }}?category=entertainment" class="hover:text-yellow-400">Entertainment</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-lg font-semibold mb-4">Informasi</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-yellow-400">Tentang Kami</a></li>
                        <li><a href="#" class="hover:text-yellow-400">Syarat & Ketentuan</a></li>
                        <li><a href="#" class="hover:text-yellow-400">Kebijakan Privasi</a></li>
                        <li><a href="#" class="hover:text-yellow-400">FAQ</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-lg font-semibold mb-4">Kontak</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li class="flex items-center space-x-2">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                            <span>support@takapedia.com</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <i data-lucide="phone" class="w-4 h-4"></i>
                            <span>+62 812-3456-7890</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <i data-lucide="message-circle" class="w-4 h-4"></i>
                            <span>WhatsApp Support</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="mt-8 pt-8 border-t border-gray-800 text-center text-gray-400">
                <p>&copy; 2024 Takapedia Clone. All rights reserved.</p>
            </div>
        </div>
    </footer>
</div>

<!-- Customer Service Float Button -->
<div class="fixed bottom-4 right-4 z-50">
    <button class="bg-yellow-400 text-gray-900 p-4 rounded-full shadow-lg hover:bg-yellow-500 transition neon-glow">
        <i data-lucide="headphones" class="w-6 h-6"></i>
    </button>
</div>

<script>
    // Initialize Lucide icons
    lucide.createIcons();

    // GSAP Animations
    gsap.from(".glass", {
        y: -20,
        opacity: 0,
        duration: 0.5,
        stagger: 0.1
    });
</script>

@stack('scripts')
</body>
</html>
