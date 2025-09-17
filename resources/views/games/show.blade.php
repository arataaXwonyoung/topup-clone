@extends('layouts.app')

@section('title', $game->name . ' - Top Up Murah')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Enhanced Game Header -->
    <div class="glass rounded-2xl p-8 mb-8 fade-in-up relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-yellow-400/10 to-orange-500/10 pointer-events-none"></div>
        <div class="flex flex-col md:flex-row items-start md:items-center space-y-6 md:space-y-0 md:space-x-8 relative z-10">
            <div class="relative group flex-shrink-0">
                <img src="{{ asset($game->cover_path) }}" alt="{{ $game->name }}" 
                     class="w-32 h-32 md:w-40 md:h-40 rounded-2xl shadow-2xl transition-all duration-300 group-hover:scale-105 group-hover:shadow-3xl">
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="absolute -top-2 -right-2 bg-gradient-to-r from-yellow-400 to-orange-500 text-gray-900 px-3 py-1 rounded-full text-sm font-bold shadow-lg">
                    TOP UP
                </div>
            </div>
            <div class="flex-1">
                <h1 class="text-3xl md:text-5xl font-bold bg-gradient-to-r from-yellow-400 via-orange-500 to-yellow-400 bg-clip-text text-transparent mb-3"
                    style="background-size: 300% 300%; animation: gradient-shift 3s ease infinite;">
                    {{ $game->name }}
                </h1>
                <p class="text-gray-300 text-xl mb-4">{{ $game->publisher }}</p>
                <div class="flex flex-wrap items-center gap-6">
                    <div class="flex items-center space-x-2">
                        <div class="flex text-yellow-400">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= floor($game->average_rating))
                                    <i data-lucide="star" class="w-5 h-5 fill-current"></i>
                                @elseif($i - 0.5 <= $game->average_rating)
                                    <i data-lucide="star-half" class="w-5 h-5 fill-current"></i>
                                @else
                                    <i data-lucide="star" class="w-5 h-5"></i>
                                @endif
                            @endfor
                        </div>
                        <span class="text-white font-semibold text-lg">{{ number_format($game->average_rating, 1) }}</span>
                        <span class="text-gray-400">({{ $game->review_count }} ulasan)</span>
                    </div>
                    <div class="flex items-center space-x-2 text-green-400">
                        <i data-lucide="zap" class="w-5 h-5"></i>
                        <span class="font-semibold">Proses Otomatis</span>
                    </div>
                    <div class="flex items-center space-x-2 text-blue-400">
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                        <span class="font-semibold">100% Aman</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 lg:gap-8">
        <!-- Left Column - Stepper Form -->
        <div class="lg:col-span-3 space-y-6">
            <form id="checkoutForm" x-data="checkoutForm()" x-ref="checkoutForm" @submit.prevent="submitOrder" @submit-order.window="submitOrder()">
                <!-- Step 1: Account Info -->
                <div class="glass rounded-xl p-6 mb-6 fade-in-up step-card border border-gray-700/50" style="animation-delay: 0.1s;">
                    <div class="flex items-center mb-6">
                        <span class="bg-gradient-to-r from-yellow-400 to-orange-500 text-gray-900 w-12 h-12 rounded-full flex items-center justify-center font-bold mr-4 shadow-lg text-lg">1</span>
                        <div>
                            <h2 class="text-2xl font-bold text-white">Masukkan Data Akun</h2>
                            <p class="text-gray-400 text-sm">Masukkan data akun game kamu dengan benar</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold mb-3 text-gray-200">{{ $game->id_label }}</label>
                            <input type="text" 
                                   x-model="formData.account_id"
                                   name="account_id" 
                                   placeholder="Masukkan {{ $game->id_label }}"
                                   class="w-full px-4 py-4 bg-gray-800/80 rounded-lg border-2 border-gray-700 focus:border-yellow-400 focus:outline-none transition-all text-lg"
                                   required>
                        </div>
                        
                        @if($game->requires_server)
                        <div>
                            <label class="block text-sm font-semibold mb-3 text-gray-200">{{ $game->server_label }}</label>
                            <input type="text" 
                                   x-model="formData.server_id"
                                   name="server_id" 
                                   placeholder="Masukkan {{ $game->server_label }}"
                                   class="w-full px-4 py-4 bg-gray-800/80 rounded-lg border-2 border-gray-700 focus:border-yellow-400 focus:outline-none transition-all text-lg"
                                   required>
                        </div>
                        @endif
                    </div>
                </div>
                
                <!-- Step 2: Select Denomination -->
                <div class="glass rounded-xl p-6 mb-6 fade-in-up step-card border border-gray-700/50" style="animation-delay: 0.2s;">
                    <div class="flex items-center mb-6">
                        <span class="bg-gradient-to-r from-yellow-400 to-orange-500 text-gray-900 w-12 h-12 rounded-full flex items-center justify-center font-bold mr-4 shadow-lg text-lg">2</span>
                        <div>
                            <h2 class="text-2xl font-bold text-white">Pilih Nominal</h2>
                            <p class="text-gray-400 text-sm">Pilih nominal yang sesuai dengan kebutuhan kamu</p>
                        </div>
                    </div>
                    
                    
                    @php
                        $denominations = $game->denominations->where('is_active', true);
                    @endphp
                    
                    @if($denominations && $denominations->count() > 0)
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 denomination-grid">
                            @foreach($denominations as $denom)
                            <div>
                                <input type="radio"
                                       name="denomination_id"
                                       value="{{ $denom->id }}"
                                       id="denom_{{ $denom->id }}"
                                       x-model="formData.denomination_id"
                                       @change="updateSummary({{ $denom->toJson() }})"
                                       class="hidden peer">
                                <label for="denom_{{ $denom->id }}" class="block cursor-pointer">
                                    <div class="bg-gray-800 border-2 border-gray-700 rounded-lg p-4 hover:border-yellow-400 peer-checked:border-yellow-400 peer-checked:bg-yellow-400/10 transition-all duration-200 relative min-h-[110px] flex flex-col justify-between">
                                        @if($denom->is_hot ?? false)
                                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full font-bold">HOT</span>
                                        @endif
                                        
                                        @if($denom->is_promo ?? false)
                                        <span class="absolute -top-2 -left-2 bg-green-500 text-white text-xs px-2 py-1 rounded-full font-bold">PROMO</span>
                                        @endif
                                        
                                        <div>
                                            <h4 class="font-bold text-white text-sm mb-1">{{ $denom->name }}</h4>
                                            
                                            @if(isset($denom->bonus) && $denom->bonus > 0)
                                            <p class="text-xs text-green-400 mb-2">
                                                {{ $denom->amount ?? '' }}{{ $denom->bonus ? ' + ' . $denom->bonus . ' Bonus' : '' }}
                                            </p>
                                            @endif
                                        </div>
                                        
                                        <div class="mt-auto">
                                            @if(isset($denom->original_price) && $denom->original_price > $denom->price)
                                            <p class="text-xs text-gray-500 line-through mb-1">
                                                Rp {{ number_format($denom->original_price, 0, ',', '.') }}
                                            </p>
                                            @endif
                                            
                                            <p class="text-yellow-400 font-bold text-base">
                                                Rp {{ number_format($denom->price, 0, ',', '.') }}
                                            </p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="text-gray-400">
                                <i data-lucide="package-x" class="w-16 h-16 mx-auto mb-4 opacity-50"></i>
                                <p class="text-lg mb-2">Belum ada nominal tersedia</p>
                                <p class="text-sm">Game: {{ $game->name }} (ID: {{ $game->id }})</p>
                                <p class="text-sm">Silakan pilih game lain atau hubungi admin</p>
                                <a href="{{ route('home') }}" class="mt-4 inline-block px-4 py-2 bg-yellow-400 text-gray-900 rounded-lg hover:bg-yellow-500 transition">
                                    Kembali ke Beranda
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
                
                <!-- Step 3: Payment Method -->
                <div class="glass rounded-xl p-6 mb-6 fade-in-up step-card border border-gray-700/50" style="animation-delay: 0.3s;">
                    <div class="flex items-center mb-6">
                        <span class="bg-gradient-to-r from-yellow-400 to-orange-500 text-gray-900 w-12 h-12 rounded-full flex items-center justify-center font-bold mr-4 shadow-lg text-lg">3</span>
                        <div>
                            <h2 class="text-2xl font-bold text-white">Pilih Pembayaran</h2>
                            <p class="text-gray-400 text-sm">Pilih metode pembayaran yang kamu inginkan</p>
                        </div>
                    </div>
                    
                    @include('components.payment-methods')
                </div>
                
                <!-- Step 4: Promo Code -->
                <div class="glass rounded-xl p-6 mb-6 fade-in-up step-card border border-gray-700/50" style="animation-delay: 0.4s;">
                    <div class="flex items-center mb-6">
                        <span class="bg-gradient-to-r from-yellow-400 to-orange-500 text-gray-900 w-12 h-12 rounded-full flex items-center justify-center font-bold mr-4 shadow-lg text-lg">4</span>
                        <div>
                            <h2 class="text-2xl font-bold text-white">Kode Promo</h2>
                            <p class="text-gray-400 text-sm">Gunakan kode promo untuk mendapatkan diskon</p>
                        </div>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-3">
                        <input type="text" 
                               x-model="formData.promo_code"
                               placeholder="Ketik Kode Promo Kamu"
                               class="flex-1 px-4 py-3 bg-gray-800/80 rounded-lg border-2 border-gray-700 focus:border-yellow-400 focus:outline-none transition-all">
                        <button type="button" 
                                @click="applyPromo"
                                class="px-6 py-3 bg-gradient-to-r from-yellow-400 to-orange-500 text-gray-900 rounded-lg hover:from-yellow-500 hover:to-orange-600 transition font-bold whitespace-nowrap">
                            Gunakan
                        </button>
                    </div>
                    
                    <div x-show="promoMessage" 
                         x-text="promoMessage"
                         :class="promoValid ? 'text-green-400' : 'text-red-400'"
                         class="mt-2 text-sm"></div>
                    
                    <!-- Promo Suggestions -->
                    <div class="mt-6 p-4 bg-gray-800/50 rounded-lg">
                        <div class="flex items-center space-x-2 mb-3">
                            <i data-lucide="tag" class="w-5 h-5 text-yellow-400"></i>
                            <span class="font-semibold text-white">Promo Tersedia:</span>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" 
                                    @click="formData.promo_code = 'NEWUSER10'; applyPromo()"
                                    class="px-4 py-2 bg-yellow-400/20 text-yellow-400 border border-yellow-400/50 rounded-lg hover:bg-yellow-400/30 transition text-sm font-medium">
                                NEWUSER10
                            </button>
                            <button type="button" 
                                    @click="formData.promo_code = 'DISKON5'; applyPromo()"
                                    class="px-4 py-2 bg-green-400/20 text-green-400 border border-green-400/50 rounded-lg hover:bg-green-400/30 transition text-sm font-medium">
                                DISKON5
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Step 5: Contact Details -->
                <div class="glass rounded-xl p-6 mb-6 fade-in-up step-card border border-gray-700/50" style="animation-delay: 0.5s;">
                    <div class="flex items-center mb-6">
                        <span class="bg-gradient-to-r from-yellow-400 to-orange-500 text-gray-900 w-12 h-12 rounded-full flex items-center justify-center font-bold mr-4 shadow-lg text-lg">5</span>
                        <div>
                            <h2 class="text-2xl font-bold text-white">Detail Kontak</h2>
                            <p class="text-gray-400 text-sm">Masukkan email dan nomor WhatsApp aktif kamu</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold mb-3 text-gray-200">Email</label>
                            <input type="email" 
                                   x-model="formData.email"
                                   placeholder="example@gmail.com"
                                   class="w-full px-4 py-4 bg-gray-800/80 rounded-lg border-2 border-gray-700 focus:border-yellow-400 focus:outline-none transition-all text-lg"
                                   required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold mb-3 text-gray-200">No. WhatsApp</label>
                            <div class="flex">
                                <span class="px-4 py-4 bg-gray-800/80 border-2 border-r-0 border-gray-700 rounded-l-lg font-semibold">
                                    🇮🇩 +62
                                </span>
                                <input type="tel" 
                                       x-model="formData.whatsapp"
                                       placeholder="812345678"
                                       class="flex-1 px-4 py-4 bg-gray-800/80 rounded-r-lg border-2 border-gray-700 focus:border-yellow-400 focus:outline-none transition-all text-lg"
                                       required>
                            </div>
                            <p class="text-xs text-gray-400 mt-2">*Nomor ini akan dihubungi jika terjadi masalah</p>
                        </div>
                    </div>
                </div>
                
                
                <!-- Description -->
                <div class="glass rounded-xl p-6 mb-8 fade-in-up border border-gray-700/50" style="animation-delay: 0.6s;">
                    <div class="flex items-center mb-6">
                        <span class="bg-gradient-to-r from-yellow-400 to-orange-500 text-gray-900 w-12 h-12 rounded-full flex items-center justify-center font-bold mr-4 shadow-lg text-lg">ℹ️</span>
                        <div>
                            <h3 class="text-2xl font-bold text-white">Tentang {{ $game->name }}</h3>
                            <p class="text-gray-400 text-sm">Informasi lengkap tentang game ini</p>
                        </div>
                    </div>
                    <div class="prose prose-invert max-w-none">
                        <p class="text-gray-300 leading-relaxed text-lg">{{ $game->description }}</p>
                    </div>
                    
                    <!-- Game Features -->
                    <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center p-3 bg-gray-800/50 rounded-lg">
                            <i data-lucide="zap" class="w-8 h-8 text-yellow-400 mx-auto mb-2"></i>
                            <div class="text-sm font-semibold text-white">Instan</div>
                            <div class="text-xs text-gray-400">Proses otomatis</div>
                        </div>
                        <div class="text-center p-3 bg-gray-800/50 rounded-lg">
                            <i data-lucide="shield-check" class="w-8 h-8 text-green-400 mx-auto mb-2"></i>
                            <div class="text-sm font-semibold text-white">Aman</div>
                            <div class="text-xs text-gray-400">100% terpercaya</div>
                        </div>
                        <div class="text-center p-3 bg-gray-800/50 rounded-lg">
                            <i data-lucide="headphones" class="w-8 h-8 text-blue-400 mx-auto mb-2"></i>
                            <div class="text-sm font-semibold text-white">Support</div>
                            <div class="text-xs text-gray-400">24/7 online</div>
                        </div>
                        <div class="text-center p-3 bg-gray-800/50 rounded-lg">
                            <i data-lucide="award" class="w-8 h-8 text-purple-400 mx-auto mb-2"></i>
                            <div class="text-sm font-semibold text-white">Terpercaya</div>
                            <div class="text-xs text-gray-400">Ribuan customer</div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Right Column - Summary Panel -->
        <div class="lg:col-span-1">
            <div class="glass rounded-xl p-6 sticky top-8 border border-gray-700/50 fade-in-up" style="animation-delay: 0.7s;">
                <h3 class="text-xl font-bold mb-6 text-yellow-400">Ringkasan Pesanan</h3>
                
                <!-- Game Info -->
                <div class="text-center mb-6 p-4 bg-gray-800/50 rounded-lg">
                    <img src="{{ asset($game->cover_path) }}" alt="{{ $game->name }}" 
                         class="w-16 h-16 mx-auto rounded-lg mb-3 shadow-lg">
                    <h4 class="font-bold text-white mb-2">{{ $game->name }}</h4>
                    <div class="flex justify-center items-center space-x-2 text-sm">
                        <div class="flex text-yellow-400">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= floor($game->average_rating))
                                    <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                                @else
                                    <i data-lucide="star" class="w-4 h-4"></i>
                                @endif
                            @endfor
                        </div>
                        <span class="text-white font-medium">{{ number_format($game->average_rating, 1) }}</span>
                        <span class="text-gray-400">({{ $game->review_count }})</span>
                    </div>
                </div>
                
                <!-- Live Summary -->
                <div class="mb-6 summary-section" x-show="selectedDenom">
                    <h4 class="font-bold mb-4 text-white">Detail Pembelian</h4>
                    <div class="space-y-3 text-sm bg-gray-800/30 rounded-lg p-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400">Item:</span>
                            <span class="font-medium text-white" x-text="selectedDenom?.name || '-'"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400">Harga:</span>
                            <span class="font-medium text-white" x-text="selectedDenom?.price ? formatCurrency(selectedDenom.price) : '-'"></span>
                        </div>
                        <div class="flex justify-between items-center" x-show="discount > 0">
                            <span class="text-gray-400">Diskon:</span>
                            <span class="text-green-400 font-medium" x-text="formatCurrency(discount)"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400">Biaya Admin:</span>
                            <span class="font-medium text-white" x-text="formatCurrency(getFee())"></span>
                        </div>
                        <div class="border-t border-gray-600 pt-3 mt-3">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-white">Total:</span>
                                <span class="text-yellow-400 font-bold text-xl" x-text="formatCurrency(getTotal())"></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Help Section -->
                <div class="mb-6 p-4 bg-blue-500/10 border border-blue-500/20 rounded-lg">
                    <div class="flex items-center space-x-2 mb-3">
                        <i data-lucide="headphones" class="w-5 h-5 text-blue-400"></i>
                        <h4 class="font-semibold text-blue-400">Butuh Bantuan?</h4>
                    </div>
                    <p class="text-sm text-gray-300 mb-3 leading-relaxed">Tim customer service kami siap membantu kamu 24/7</p>
                    <button class="w-full py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition font-medium text-sm">
                        <i data-lucide="message-circle" class="inline w-4 h-4 mr-1"></i>
                        Hubungi Admin
                    </button>
                </div>
                
                <!-- Order Button -->
                <button type="button" 
                        @click="showConfirmModal()"
                         class="w-full py-4 bg-gradient-to-r from-yellow-400 to-orange-500 text-gray-900 rounded-xl font-bold text-lg hover:from-yellow-500 hover:to-orange-600 transition duration-300 transform hover:scale-105 shadow-lg">
                    <i data-lucide="shopping-cart" class="inline w-5 h-5 mr-2"></i>
                    Pesan Sekarang!
                </button>
                
                <!-- Security Info -->
                <div class="mt-4 text-center">
                    <div class="flex items-center justify-center space-x-4 text-xs text-gray-400">
                        <div class="flex items-center space-x-1">
                            <i data-lucide="shield" class="w-4 h-4"></i>
                            <span>SSL Secured</span>
                        </div>
                        <div class="flex items-center space-x-1">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                            <span>Data Protected</span>
                        </div>
                        <div class="flex items-center space-x-1">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            <span>Trusted</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Reviews Section -->
    @if($reviews->count() > 0)
    <div class="mt-12">
        <div class="glass rounded-xl p-6 border border-gray-700/50">
            <h3 class="text-2xl font-bold mb-6 text-white">Ulasan Terbaru</h3>
            <div class="space-y-6">
                @foreach($reviews as $review)
                <div class="bg-gray-800/50 rounded-lg p-5 border border-gray-700/30">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full flex items-center justify-center font-bold text-gray-900">
                                {{ strtoupper(substr($review->user ? $review->user->name : 'A', 0, 1)) }}
                            </div>
                            <div>
                                <span class="font-semibold text-white">{{ $review->user ? $review->user->name : 'Anonymous' }}</span>
                                <div class="flex text-yellow-400 mt-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $review->rating)
                                            <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                                        @else
                                            <i data-lucide="star" class="w-4 h-4"></i>
                                        @endif
                                    @endfor
                                </div>
                            </div>
                        </div>
                        <span class="text-xs text-gray-500 bg-gray-700/50 px-2 py-1 rounded">{{ $review->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-gray-300 leading-relaxed">{{ $review->comment }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Confirmation Modal -->
<div id="confirmModal"
     x-data="{ open: false }"
     x-show="open"
     @open-confirm.window="open = true"
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     x-cloak>
    <div class="fixed inset-0 bg-black opacity-50 modal-backdrop" @click="open = false"></div>
    
    <div class="relative glass rounded-xl p-6 max-w-md w-full" 
         @click.stop
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-90"
         x-transition:enter-end="opacity-100 transform scale-100">
        
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <i data-lucide="check" class="w-8 h-8 text-white"></i>
            </div>
            <h3 class="text-xl font-semibold mb-2">Buat Pesanan</h3>
            <p class="text-gray-400">Pastikan data akun Kamu dan produk yang Kamu pilih valid dan sesuai.</p>
        </div>
        
        <div class="space-y-3 mb-6 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-400">Username:</span>
                <span id="confirm-username">-</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-400">ID:</span>
                <span id="confirm-id">-</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-400">Server:</span>
                <span id="confirm-server">-</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-400">Item:</span>
                <span id="confirm-item">-</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-400">Product:</span>
                <span>{{ $game->name }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-400">Payment:</span>
                <span id="confirm-payment">-</span>
            </div>
        </div>
        
        <div class="flex items-center mb-6">
            <input type="checkbox" id="terms" class="mr-2">
            <label for="terms" class="text-sm text-gray-400">
                Dengan mengklik Pesan Sekarang!, kamu sudah menyetujui Syarat & Ketentuan yang berlaku
            </label>
        </div>
        
        <div class="flex space-x-3">
            <button @click="$dispatch('submit-order')"
                    class="flex-1 py-2 bg-yellow-400 text-gray-900 rounded-lg font-semibold hover:bg-yellow-500 transition">
                Pesan Sekarang!
            </button>
            <button @click="open = false" 
                    class="flex-1 py-2 border border-gray-600 rounded-lg hover:bg-gray-700 transition">
                Batalkan
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>
<script>
function checkoutForm() {
    return {
        formData: {
            game_id: {{ $game->id }},
            account_id: '',
            server_id: '',
            denomination_id: null,
            promo_code: '',
            payment_method: '',
            payment_channel: '',
            email: '',
            whatsapp: ''
        },
        selectedDenom: null,
        discount: 0,
        promoMessage: '',
        promoValid: false,
        
        updateSummary(denom) {
            this.selectedDenom = denom;
        },
        
        getFee() {
            const fees = {
                'QRIS': 1000,
                'VA': 2500,
                'EWALLET': 1500,
                'CREDIT_CARD': 5000,
                'CSTORE': 2500
            };
            return fees[this.formData.payment_method] || 0;
        },
        
        getTotal() {
            if (!this.selectedDenom) return 0;
            return (this.selectedDenom.price - this.discount + this.getFee());
        },
        
        formatCurrency(amount) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
        },
        
        async applyPromo() {
            if (!this.formData.promo_code || !this.selectedDenom) {
                this.promoMessage = 'Pilih item terlebih dahulu';
                this.promoValid = false;
                return;
            }
            
            try {
                const response = await fetch('{{ route("checkout.validate-promo") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        promo_code: this.formData.promo_code,
                        denomination_id: this.formData.denomination_id,
                        quantity: 1
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.discount = data.discount;
                    this.promoMessage = data.message;
                    this.promoValid = true;
                } else {
                    this.discount = 0;
                    this.promoMessage = data.message;
                    this.promoValid = false;
                }
            } catch (error) {
                this.promoMessage = 'Gagal memvalidasi promo';
                this.promoValid = false;
            }
        },
        
        showConfirmModal() {
            if (!this.validateForm()) {
                return;
            }
            
            // Update modal content
            document.getElementById('confirm-username').textContent = this.formData.account_id || '-';
            document.getElementById('confirm-id').textContent = this.formData.account_id;
            document.getElementById('confirm-server').textContent = this.formData.server_id || '-';
            document.getElementById('confirm-item').textContent = this.selectedDenom?.name || '-';
            document.getElementById('confirm-payment').textContent = this.formData.payment_method;
            
            // Dispatch event to open modal
            window.dispatchEvent(new CustomEvent('open-confirm'));
        },
        
        validateForm() {
            if (!this.formData.account_id) {
                alert('Masukkan User ID');
                return false;
            }
            
            if ({{ $game->requires_server ? 'true' : 'false' }} && !this.formData.server_id) {
                alert('Masukkan Server ID');
                return false;
            }
            
            if (!this.formData.denomination_id) {
                alert('Pilih nominal');
                return false;
            }
            
            if (!this.formData.payment_method) {
                alert('Pilih metode pembayaran');
                return false;
            }
            
            if (!this.formData.email || !this.formData.whatsapp) {
                alert('Lengkapi detail kontak');
                return false;
            }
            
            return true;
        },
        
        async submitOrder() {
            if (!document.getElementById('terms').checked) {
                alert('Anda harus menyetujui syarat & ketentuan');
                return;
            }
            
            // Get reCAPTCHA token
            grecaptcha.ready(() => {
                grecaptcha.execute('{{ config("services.recaptcha.site_key") }}', {action: 'checkout'})
                    .then(async (token) => {
                        this.formData['g-recaptcha-response'] = token;
                        
                        try {
                            const response = await fetch('{{ route("checkout.process") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify(this.formData)
                            });
                            
                            const data = await response.json();
                            
                            if (data.success) {
                                // Trigger confetti
                                confetti({
                                    particleCount: 100,
                                    spread: 70,
                                    origin: { y: 0.6 }
                                });
                                
                                // Redirect to invoice
                                setTimeout(() => {
                                    window.location.href = data.redirect_url;
                                }, 1500);
                            } else {
                                alert(data.message || 'Terjadi kesalahan');
                            }
                        } catch (error) {
                            alert('Gagal memproses pesanan');
                        }
                    });
            });
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // Simple animation for step cards
    const cards = document.querySelectorAll('.fade-in-up');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'all 0.6s ease';

        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 200);
    });

    console.log('Game detail page loaded');
});
</script>

<style>
[x-cloak] {
    display: none !important;
}

/* Ensure denomination cards display properly */
.denomination-grid {
    display: grid !important;
    visibility: visible !important;
    opacity: 1 !important;
}

/* Fix payment method visibility */
.payment-method-option {
    display: block !important;
    visibility: visible !important;
}

/* Ensure modal backdrop works correctly */
.modal-backdrop {
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(2px);
}
</style>

