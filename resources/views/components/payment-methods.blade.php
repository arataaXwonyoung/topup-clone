<div class="space-y-4">
    <!-- QRIS -->
    <div class="mb-4">
        <label class="cursor-pointer">
            <input type="radio" 
                   name="payment_method" 
                   value="QRIS"
                   x-model="formData.payment_method"
                   class="hidden peer">
            <div class="glass p-5 rounded-xl border-2 border-gray-700 peer-checked:border-yellow-400 peer-checked:bg-yellow-400/20 peer-checked:shadow-lg peer-checked:shadow-yellow-400/20 hover:border-gray-600 transition-all transform hover:scale-[1.02]">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h.01"></path>
                            </svg>
                        </div>
                        <div>
                            <span class="font-bold text-lg text-white">QRIS (All Payment)</span>
                            <p class="text-sm text-gray-400">Bayar dengan semua e-wallet & bank</p>
                            <div class="flex items-center space-x-2 mt-1">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/e1/Logo_of_Dana_%28payment_system%29.svg/512px-Logo_of_Dana_%28payment_system%29.svg.png" alt="DANA" class="h-4">
                                <img src="https://logos-world.net/wp-content/uploads/2020/09/OVO-Logo.png" alt="OVO" class="h-4">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/8/86/Gopay_logo.svg" alt="GoPay" class="h-4">
                                <span class="text-xs text-gray-500">+more</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-yellow-400 font-bold text-lg">+Rp 1.000</span>
                        <p class="text-xs text-gray-400">Biaya admin</p>
                        <span class="text-xs bg-green-500 text-white px-2 py-1 rounded mt-1 inline-block">Instant</span>
                    </div>
                </div>
            </div>
        </label>
    </div>
    
    <!-- E-Wallet Section -->
    <div class="mb-4" x-data="{ open: false }">
        <button type="button" 
                @click="open = !open"
                class="w-full text-left glass p-5 rounded-xl border-2 border-gray-700 hover:border-gray-600 transition-all">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-green-500 rounded-lg flex items-center justify-center">
                        <i data-lucide="smartphone" class="w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <span class="font-bold text-lg text-white">E-Wallet</span>
                        <p class="text-sm text-gray-400">DANA, OVO, GoPay, ShopeePay</p>
                    </div>
                </div>
                <i data-lucide="chevron-down" class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open }"></i>
            </div>
        </button>
        
        <div x-show="open" x-transition class="mt-3 space-y-3 ml-4">
            @php 
            $ewallets = [
                'DANA' => ['code' => 'dana', 'fee' => 1500, 'logo' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e1/Logo_of_Dana_%28payment_system%29.svg/512px-Logo_of_Dana_%28payment_system%29.svg.png'],
                'OVO' => ['code' => 'ovo', 'fee' => 1500, 'logo' => 'https://logos-world.net/wp-content/uploads/2020/09/OVO-Logo.png'],
                'GoPay' => ['code' => 'gopay', 'fee' => 1500, 'logo' => 'https://upload.wikimedia.org/wikipedia/commons/8/86/Gopay_logo.svg'],
                'ShopeePay' => ['code' => 'shopeepay', 'fee' => 1500, 'logo' => 'https://logos-world.net/wp-content/uploads/2021/02/Shopee-Logo.png']
            ];
            @endphp
            
            @foreach($ewallets as $name => $ewallet)
            <label class="cursor-pointer block">
                <input type="radio" 
                       name="payment_method" 
                       value="EWALLET"
                       x-model="formData.payment_method"
                       @change="formData.payment_channel = '{{ $ewallet['code'] }}'"
                       class="hidden peer">
                <div class="glass p-4 rounded-lg border-2 border-gray-700 peer-checked:border-yellow-400 peer-checked:bg-yellow-400/20 hover:border-gray-600 transition-all">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <img src="{{ $ewallet['logo'] }}" alt="{{ $name }}" class="h-8 w-8 object-contain">
                            <span class="font-semibold text-white">{{ $name }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-yellow-400 font-bold">+Rp {{ number_format($ewallet['fee']) }}</span>
                            <p class="text-xs text-gray-400">Biaya admin</p>
                        </div>
                    </div>
                </div>
            </label>
            @endforeach
        </div>
    </div>
    
    <!-- Virtual Account Section -->
    <div x-data="{ open: false }">
        <button type="button" 
                @click="open = !open"
                class="w-full text-left glass p-5 rounded-xl border-2 border-gray-700 hover:border-gray-600 transition-all">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-r from-red-500 to-orange-500 rounded-lg flex items-center justify-center">
                        <i data-lucide="building" class="w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <span class="font-bold text-lg text-white">Virtual Account</span>
                        <p class="text-sm text-gray-400">BCA, BNI, BRI, Mandiri, CIMB</p>
                        <span class="text-xs bg-blue-500 text-white px-2 py-1 rounded mt-1 inline-block">24 Jam</span>
                    </div>
                </div>
                <i data-lucide="chevron-down" class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open }"></i>
            </div>
        </button>
        
        <div x-show="open" x-transition class="mt-3 space-y-3 ml-4">
            @php 
            $banks = [
                'BCA' => ['code' => 'bca', 'fee' => 2500, 'logo' => 'https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia.svg'],
                'BNI' => ['code' => 'bni', 'fee' => 2500, 'logo' => 'https://upload.wikimedia.org/wikipedia/en/2/2e/BNI_logo.svg'],
                'BRI' => ['code' => 'bri', 'fee' => 2500, 'logo' => 'https://upload.wikimedia.org/wikipedia/commons/2/2e/BRI_2020.svg'],
                'Mandiri' => ['code' => 'mandiri', 'fee' => 2500, 'logo' => 'https://upload.wikimedia.org/wikipedia/commons/a/ad/Bank_Mandiri_logo_2016.svg'],
                'CIMB Niaga' => ['code' => 'cimb', 'fee' => 2500, 'logo' => 'https://logos-world.net/wp-content/uploads/2021/02/CIMB-Logo.png']
            ];
            @endphp
            
            @foreach($banks as $name => $bank)
            <label class="cursor-pointer block">
                <input type="radio" 
                       name="payment_method" 
                       value="VA"
                       x-model="formData.payment_method"
                       @change="formData.payment_channel = '{{ $bank['code'] }}'"
                       class="hidden peer">
                <div class="glass p-4 rounded-lg border-2 border-gray-700 peer-checked:border-yellow-400 peer-checked:bg-yellow-400/20 hover:border-gray-600 transition-all">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <img src="{{ $bank['logo'] }}" alt="{{ $name }}" class="h-8 w-12 object-contain">
                            <span class="font-semibold text-white">{{ $name }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-yellow-400 font-bold">+Rp {{ number_format($bank['fee']) }}</span>
                            <p class="text-xs text-gray-400">Biaya admin</p>
                        </div>
                    </div>
                </div>
            </label>
            @endforeach
        </div>
    </div>
    
    <!-- Credit Card Section -->
    <div class="mb-4">
        <label class="cursor-pointer">
            <input type="radio" 
                   name="payment_method" 
                   value="CREDIT_CARD"
                   x-model="formData.payment_method"
                   class="hidden peer">
            <div class="glass p-5 rounded-xl border-2 border-gray-700 peer-checked:border-yellow-400 peer-checked:bg-yellow-400/20 peer-checked:shadow-lg peer-checked:shadow-yellow-400/20 hover:border-gray-600 transition-all transform hover:scale-[1.02]">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <i data-lucide="credit-card" class="w-6 h-6 text-white"></i>
                        </div>
                        <div>
                            <span class="font-bold text-lg text-white">Credit Card</span>
                            <p class="text-sm text-gray-400">Visa, MasterCard, JCB</p>
                            <div class="flex items-center space-x-2 mt-1">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/5/5e/Visa_Inc._logo.svg" alt="Visa" class="h-4">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" alt="MasterCard" class="h-4">
                                <span class="text-xs bg-purple-500 text-white px-2 py-1 rounded">Secure</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-yellow-400 font-bold text-lg">+Rp 5.000</span>
                        <p class="text-xs text-gray-400">Biaya admin</p>
                        <span class="text-xs bg-green-500 text-white px-2 py-1 rounded mt-1 inline-block">Instant</span>
                    </div>
                </div>
            </div>
        </label>
    </div>
    
    <!-- Convenience Store -->
    <div class="mb-4" x-data="{ open: false }">
        <button type="button" 
                @click="open = !open"
                class="w-full text-left glass p-5 rounded-xl border-2 border-gray-700 hover:border-gray-600 transition-all">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-teal-500 rounded-lg flex items-center justify-center">
                        <i data-lucide="store" class="w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <span class="font-bold text-lg text-white">Convenience Store</span>
                        <p class="text-sm text-gray-400">Indomaret, Alfamart</p>
                    </div>
                </div>
                <i data-lucide="chevron-down" class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open }"></i>
            </div>
        </button>
        
        <div x-show="open" x-transition class="mt-3 space-y-3 ml-4">
            @php 
            $stores = [
                'Indomaret' => ['code' => 'indomaret', 'fee' => 2500, 'logo' => 'https://upload.wikimedia.org/wikipedia/commons/9/9d/Logo_Indomaret.png'],
                'Alfamart' => ['code' => 'alfamart', 'fee' => 2500, 'logo' => 'https://logos-world.net/wp-content/uploads/2020/11/Alfamart-Logo.png']
            ];
            @endphp
            
            @foreach($stores as $name => $store)
            <label class="cursor-pointer block">
                <input type="radio" 
                       name="payment_method" 
                       value="CSTORE"
                       x-model="formData.payment_method"
                       @change="formData.payment_channel = '{{ $store['code'] }}'"
                       class="hidden peer">
                <div class="glass p-4 rounded-lg border-2 border-gray-700 peer-checked:border-yellow-400 peer-checked:bg-yellow-400/20 hover:border-gray-600 transition-all">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <img src="{{ $store['logo'] }}" alt="{{ $name }}" class="h-8 w-12 object-contain">
                            <span class="font-semibold text-white">{{ $name }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-yellow-400 font-bold">+Rp {{ number_format($store['fee']) }}</span>
                            <p class="text-xs text-gray-400">Biaya admin</p>
                        </div>
                    </div>
                </div>
            </label>
            @endforeach
        </div>
    </div>
</div>

<style>
.glass {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.glass:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
}

.peer:checked + .glass {
    transform: scale(1.02);
    border-color: #fbbf24;
}

input[type="radio"]:checked + .glass::before {
    content: "✓";
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 24px;
    height: 24px;
    background: #fbbf24;
    color: #1f2937;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
    z-index: 10;
}
</style>