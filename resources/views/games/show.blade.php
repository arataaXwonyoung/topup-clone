@extends('layouts.app')

@section('title', $game->name . ' - Top Up Murah')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Game Header -->
    <div class="glass rounded-xl p-6 mb-8">
        <div class="flex items-center space-x-4">
            <img src="{{ $game->cover_path }}" alt="{{ $game->name }}" class="w-20 h-20 rounded-lg">
            <div>
                <h1 class="text-2xl font-bold text-yellow-400">{{ $game->name }}</h1>
                <p class="text-gray-400">{{ $game->publisher }}</p>
                <div class="flex items-center mt-2">
                    <div class="flex text-yellow-400">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= floor($game->average_rating))
                                <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                            @elseif($i - 0.5 <= $game->average_rating)
                                <i data-lucide="star-half" class="w-4 h-4 fill-current"></i>
                            @else
                                <i data-lucide="star" class="w-4 h-4"></i>
                            @endif
                        @endfor
                    </div>
                    <span class="ml-2 text-sm text-gray-400">{{ number_format($game->average_rating, 1) }} ({{ $game->review_count }} ulasan)</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column - Stepper Form -->
        <div class="lg:col-span-2">
            <form id="checkoutForm" x-data="checkoutForm()" @submit.prevent="submitOrder">
                <!-- Step 1: Account Info -->
                <div class="glass rounded-xl p-6 mb-6">
                    <div class="flex items-center mb-4">
                        <span class="bg-yellow-400 text-gray-900 w-8 h-8 rounded-full flex items-center justify-center font-bold mr-3">1</span>
                        <h2 class="text-lg font-semibold">Masukkan Data Akun</h2>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">{{ $game->id_label }}</label>
                            <input type="text" 
                                   x-model="formData.account_id"
                                   name="account_id" 
                                   placeholder="Masukkan {{ $game->id_label }}"
                                   class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none"
                                   required>
                        </div>
                        
                        @if($game->requires_server)
                        <div>
                            <label class="block text-sm font-medium mb-2">{{ $game->server_label }}</label>
                            <input type="text" 
                                   x-model="formData.server_id"
                                   name="server_id" 
                                   placeholder="Masukkan {{ $game->server_label }}"
                                   class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none"
                                   required>
                        </div>
                        @endif
                    </div>
                </div>
                
                <!-- Step 2: Select Denomination -->
                <div class="glass rounded-xl p-6 mb-6">
    <div class="flex items-center mb-4">
        <span class="bg-yellow-400 text-gray-900 w-8 h-8 rounded-full flex items-center justify-center font-bold mr-3">2</span>
        <h2 class="text-lg font-semibold">Pilih Nominal</h2>
    </div>
    
    @if($game->denominations && $game->denominations->count() > 0)
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            @foreach($game->denominations as $denom)
            <label class="relative cursor-pointer">
                <input type="radio" 
                       name="denomination_id" 
                       value="{{ $denom->id }}"
                       x-model="formData.denomination_id"
                       @change="updateSummary({{ $denom->toJson() }})"
                       class="hidden peer">
                <div class="glass p-4 rounded-lg border-2 border-gray-700 peer-checked:border-yellow-400 peer-checked:bg-yellow-400/10 hover:border-gray-600 transition">
                    @if($denom->is_hot)
                    <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs px-2 py-1 rounded font-bold">HOT</span>
                    @endif
                    <div class="font-semibold text-white">{{ $denom->name }}</div>
                    @if($denom->bonus > 0)
                    <div class="text-xs text-gray-400">{{ $denom->amount }} + {{ $denom->bonus }} Bonus</div>
                    @endif
                    <div class="text-yellow-400 font-bold mt-2">Rp {{ number_format($denom->price, 0, ',', '.') }}</div>
                </div>
            </label>
            @endforeach
        </div>
    @else
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-200">Tidak ada nominal tersedia</h3>
            <p class="mt-1 text-sm text-gray-400">Nominal untuk game ini sedang tidak tersedia.</p>
        </div>
    @endif
</div>
                
                <!-- Step 3: Summary -->
                <div class="glass rounded-xl p-6 mb-6">
                    <div class="flex items-center mb-4">
                        <span class="bg-yellow-400 text-gray-900 w-8 h-8 rounded-full flex items-center justify-center font-bold mr-3">3</span>
                        <h2 class="text-lg font-semibold">Ringkasan</h2>
                    </div>
                    
                    <div class="space-y-2" x-show="selectedDenom">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Item:</span>
                            <span x-text="selectedDenom?.name"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Harga:</span>
                            <span x-text="formatCurrency(selectedDenom?.price)"></span>
                        </div>
                    </div>
                </div>
                
                <!-- Step 4: Promo Code -->
                <div class="glass rounded-xl p-6 mb-6">
                    <div class="flex items-center mb-4">
                        <span class="bg-yellow-400 text-gray-900 w-8 h-8 rounded-full flex items-center justify-center font-bold mr-3">4</span>
                        <h2 class="text-lg font-semibold">Kode Promo</h2>
                    </div>
                    
                    <div class="flex space-x-2">
                        <input type="text" 
                               x-model="formData.promo_code"
                               placeholder="Ketik Kode Promo Kamu"
                               class="flex-1 px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none">
                        <button type="button" 
                                @click="applyPromo"
                                class="px-6 py-2 bg-yellow-400 text-gray-900 rounded-lg hover:bg-yellow-500 transition">
                            Gunakan
                        </button>
                    </div>
                    
                    <div x-show="promoMessage" 
                         x-text="promoMessage"
                         :class="promoValid ? 'text-green-400' : 'text-red-400'"
                         class="mt-2 text-sm"></div>
                    
                    <!-- Promo Suggestions -->
                    <div class="mt-4 flex items-center space-x-2">
                        <i data-lucide="tag" class="w-4 h-4 text-yellow-400"></i>
                        <button type="button" 
                                @click="formData.promo_code = 'NEWUSER10'; applyPromo()"
                                class="text-sm text-yellow-400 hover:underline">
                            Pakai Promo Yang Tersedia
                        </button>
                    </div>
                </div>
                
                <!-- Step 5: Payment Method -->
                <div class="glass rounded-xl p-6 mb-6">
                    <div class="flex items-center mb-4">
                        <span class="bg-yellow-400 text-gray-900 w-8 h-8 rounded-full flex items-center justify-center font-bold mr-3">5</span>
                        <h2 class="text-lg font-semibold">Pilih Pembayaran</h2>
                    </div>
                    
                    <!-- QRIS -->
                    <div class="mb-4">
                        <label class="cursor-pointer">
                            <input type="radio" 
                                   name="payment_method" 
                                   value="QRIS"
                                   x-model="formData.payment_method"
                                   class="hidden peer">
                            <div class="glass p-4 rounded-lg border-2 border-gray-700 peer-checked:border-yellow-400 peer-checked:bg-yellow-400/10 hover:border-gray-600 transition">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <img src="/images/qris-logo.png" alt="QRIS" class="h-8">
                                        <span class="font-semibold">QRIS (All Payment)</span>
                                    </div>
                                    <span class="text-yellow-400">Rp 1.000</span>
                                </div>
                            </div>
                        </label>
                    </div>
                    
                    <!-- E-Wallet -->
                    <div class="mb-4" x-data="{ open: false }">
                        <button type="button" 
                                @click="open = !open"
                                class="w-full text-left glass p-4 rounded-lg border-2 border-gray-700 hover:border-gray-600 transition">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold">E-Wallet</span>
                                <i data-lucide="chevron-down" class="w-5 h-5" :class="{ 'rotate-180': open }"></i>
                            </div>
                        </button>
                        
                        <div x-show="open" x-transition class="mt-2 space-y-2">
                            @foreach(['DANA' => 'dana', 'OVO' => 'ovo', 'GoPay' => 'gopay', 'ShopeePay' => 'shopeepay'] as $name => $value)
                            <label class="cursor-pointer">
                                <input type="radio" 
                                       name="payment_method" 
                                       value="EWALLET"
                                       x-model="formData.payment_method"
                                       @change="formData.payment_channel = '{{ $value }}'"
                                       class="hidden peer">
                                <div class="glass p-3 rounded-lg border-2 border-gray-700 peer-checked:border-yellow-400 peer-checked:bg-yellow-400/10 hover:border-gray-600 transition ml-4">
                                    <div class="flex items-center justify-between">
                                        <span>{{ $name }}</span>
                                        <span class="text-yellow-400">Rp 1.500</span>
                                    </div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Virtual Account -->
                    <div x-data="{ open: false }">
                        <button type="button" 
                                @click="open = !open"
                                class="w-full text-left glass p-4 rounded-lg border-2 border-gray-700 hover:border-gray-600 transition">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold">Virtual Account</span>
                                <i data-lucide="chevron-down" class="w-5 h-5" :class="{ 'rotate-180': open }"></i>
                            </div>
                        </button>
                        
                        <div x-show="open" x-transition class="mt-2 space-y-2">
                            @foreach(['BCA' => 'bca', 'BNI' => 'bni', 'BRI' => 'bri', 'Mandiri' => 'mandiri'] as $name => $value)
                            <label class="cursor-pointer">
                                <input type="radio" 
                                       name="payment_method" 
                                       value="VA"
                                       x-model="formData.payment_method"
                                       @change="formData.payment_channel = '{{ $value }}'"
                                       class="hidden peer">
                                <div class="glass p-3 rounded-lg border-2 border-gray-700 peer-checked:border-yellow-400 peer-checked:bg-yellow-400/10 hover:border-gray-600 transition ml-4">
                                    <div class="flex items-center justify-between">
                                        <span>{{ $name }}</span>
                                        <span class="text-yellow-400">Rp 2.500</span>
                                    </div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                <!-- Step 6: Contact Details -->
                <div class="glass rounded-xl p-6 mb-6">
                    <div class="flex items-center mb-4">
                        <span class="bg-yellow-400 text-gray-900 w-8 h-8 rounded-full flex items-center justify-center font-bold mr-3">6</span>
                        <h2 class="text-lg font-semibold">Detail Kontak</h2>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Email</label>
                            <input type="email" 
                                   x-model="formData.email"
                                   placeholder="example@gmail.com"
                                   class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none"
                                   required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-2">No. WhatsApp</label>
                            <div class="flex">
                                <span class="px-3 py-2 bg-gray-800 border border-r-0 border-gray-700 rounded-l-lg">
                                    🇮🇩 +62
                                </span>
                                <input type="tel" 
                                       x-model="formData.whatsapp"
                                       placeholder="812345678"
                                       class="flex-1 px-4 py-2 bg-gray-800 rounded-r-lg border border-gray-700 focus:border-yellow-400 focus:outline-none"
                                       required>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">*Nomor ini akan dihubungi jika terjadi masalah</p>
                        </div>
                    </div>
                </div>
                
                <!-- Description -->
                <div class="glass rounded-xl p-6">
                    <h3 class="text-lg font-semibold mb-4 text-yellow-400">Deskripsi {{ $game->name }}</h3>
                    <p class="text-gray-400">{{ $game->description }}</p>
                </div>
            </form>
        </div>
        
        <!-- Right Column - Summary Panel -->
        <div class="lg:col-span-1">
            <div class="glass rounded-xl p-6 sticky top-20">
                <h3 class="text-lg font-semibold mb-4">Ulasan dan Rating</h3>
                
                <div class="text-center mb-6">
                    <div class="text-4xl font-bold text-yellow-400">{{ number_format($game->average_rating, 1) }}</div>
                    <div class="flex justify-center text-yellow-400 my-2">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= floor($game->average_rating))
                                <i data-lucide="star" class="w-5 h-5 fill-current"></i>
                            @else
                                <i data-lucide="star" class="w-5 h-5"></i>
                            @endif
                        @endfor
                    </div>
                    <p class="text-sm text-gray-400">Berdasarkan total {{ $game->review_count }} rating</p>
                </div>
                
                <div class="mb-6">
                    <h4 class="font-semibold mb-2">Butuh Bantuan?</h4>
                    <p class="text-sm text-gray-400">Kamu bisa hubungi admin disini.</p>
                </div>
                
                <!-- Live Summary -->
                <div class="border-t border-gray-700 pt-4" x-data="checkoutForm()" x-show="selectedDenom">
                    <h4 class="font-semibold mb-3">Detail Pembelian</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Produk:</span>
                            <span>{{ $game->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Item:</span>
                            <span x-text="selectedDenom?.name"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Harga:</span>
                            <span x-text="formatCurrency(selectedDenom?.price)"></span>
                        </div>
                        <div class="flex justify-between" x-show="discount > 0">
                            <span class="text-gray-400">Diskon:</span>
                            <span class="text-green-400" x-text="'-' + formatCurrency(discount)"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Biaya Admin:</span>
                            <span x-text="formatCurrency(getFee())"></span>
                        </div>
                        <div class="border-t border-gray-700 pt-2 mt-2">
                            <div class="flex justify-between font-semibold text-lg">
                                <span>Total:</span>
                                <span class="text-yellow-400" x-text="formatCurrency(getTotal())"></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Order Button -->
                <button type="button" 
                        @click="showConfirmModal()"
                         class="w-full mt-6 py-3 bg-yellow-400 text-gray-900 rounded-lg font-semibold hover:bg-yellow-500 transition neon-glow">
                    <i data-lucide="shopping-cart" class="inline w-5 h-5 mr-2"></i>
                    Pesan Sekarang!
                </button>
            </div>
        </div>
    </div>
    
    <!-- Reviews Section -->
    @if($reviews->count() > 0)
    <div class="mt-12">
        <h3 class="text-xl font-semibold mb-6">Ulasan Terbaru</h3>
        <div class="space-y-4">
            @foreach($reviews as $review)
            <div class="glass rounded-lg p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center space-x-2 mb-2">
                            <span class="font-semibold">{{ $review->user ? $review->user->name : 'Anonymous' }}</span>
                            <div class="flex text-yellow-400">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $review->rating)
                                        <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                                    @else
                                        <i data-lucide="star" class="w-4 h-4"></i>
                                    @endif
                                @endfor
                            </div>
                        </div>
                        <p class="text-gray-400">{{ $review->comment }}</p>
                    </div>
                    <span class="text-xs text-gray-500">{{ $review->created_at->diffForHumans() }}</span>
                </div>
            </div>
            @endforeach
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
     style="display: none;">
    <div class="fixed inset-0 bg-black opacity-50" @click="open = false"></div>
    
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
            <button @click="$refs.checkoutForm.submit()" 
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
                'EWALLET': 1500
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
</script>
@endpush