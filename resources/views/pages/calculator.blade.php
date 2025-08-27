@extends('layouts.app')

@section('title', 'Kalkulator - Hitung Budget Top Up')

@push('styles')
<style>
    /* Calculator specific fixes */
    .calculator-result {
        position: relative !important;
        z-index: 1000 !important;
        visibility: visible !important;
        opacity: 1 !important;
        display: block !important;
    }
    
    .calculator-result * {
        position: relative !important;
        z-index: 1000 !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    /* Force visibility for Alpine.js elements */
    [x-show] {
        display: block !important;
    }
    
    .glass {
        position: relative !important;
        z-index: 10 !important;
    }
    
    /* Ensure calculator content is always visible */
    .calculator-content {
        background: rgba(30, 30, 40, 0.95) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        position: relative !important;
        z-index: 100 !important;
    }
    
    /* Make sure calculation display is visible */
    .calculation-display {
        background: rgba(40, 40, 50, 0.9) !important;
        border: 1px solid rgba(255, 234, 0, 0.3) !important;
        position: relative !important;
        z-index: 200 !important;
        min-height: 100px !important;
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-8 py-4 sm:py-8">
    <div class="glass rounded-xl p-4 sm:p-6 mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-yellow-400 mb-4">Kalkulator Top Up</h1>
        <p class="text-gray-400 text-sm sm:text-base">Hitung budget yang kamu butuhkan untuk top up game favoritmu!</p>
    </div>

    <div x-data="calculator()" class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-8">
        <!-- Calculator Form -->
        <div class="glass rounded-xl p-4 sm:p-6">
            <h2 class="text-lg sm:text-xl font-semibold mb-4">Pilih Game & Item</h2>
            
            <div class="space-y-4">
                <!-- Select Game -->
                <div>
                    <label class="block text-sm font-medium mb-2">Pilih Game</label>
                    <select x-model="selectedGame" 
                            @change="updateDenominations()"
                            class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none">
                        <option value="">-- Pilih Game --</option>
                        @foreach($games as $game)
                        <option value="{{ $game->id }}" 
                                data-denominations='@json($game->denominations)'>
                            {{ $game->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Select Items -->
                <div x-show="denominations.length > 0">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium">Pilih Item</label>
                        <button @click="clearAll()" 
                                x-show="selectedItems.length > 0"
                                class="text-xs text-red-400 hover:text-red-300 underline">
                            Clear All
                        </button>
                    </div>
                    <div class="space-y-2 max-h-60 overflow-y-auto">
                        <template x-for="denom in denominations" :key="denom.id">
                            <label class="flex items-center justify-between p-3 bg-gray-800 rounded-lg cursor-pointer hover:bg-gray-700 transition-all"
                                   :class="{ 'ring-2 ring-yellow-400 bg-yellow-400 bg-opacity-10': isItemSelected(denom.id) }">
                                <div class="flex items-center flex-1">
                                    <input type="checkbox" 
                                           :value="denom.id"
                                           :checked="isItemSelected(denom.id)"
                                           @change="toggleItem(denom)"
                                           class="mr-3 text-yellow-400 focus:ring-yellow-400 focus:ring-2">
                                    <div class="flex-1">
                                        <div class="font-medium" x-text="denom.name"></div>
                                        <div class="text-xs text-gray-400" x-text="formatCurrency(denom.price)"></div>
                                        <div class="text-xs text-yellow-400" x-show="isItemSelected(denom.id)" x-text="'Selected: ' + getItemQuantity(denom.id) + 'x'"></div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button @click.prevent="updateQuantity(denom.id, Math.max(1, getItemQuantity(denom.id) - 1))"
                                            x-show="isItemSelected(denom.id)"
                                            class="w-8 h-8 bg-gray-700 hover:bg-gray-600 rounded-full flex items-center justify-center text-sm font-bold">
                                        -
                                    </button>
                                    <input type="number" 
                                           :value="getItemQuantity(denom.id)"
                                           @input="updateQuantity(denom.id, $event.target.value)"
                                           @click.stop
                                           min="1" 
                                           max="999"
                                           class="w-16 px-2 py-1 bg-gray-700 rounded text-center text-sm">
                                    <button @click.prevent="updateQuantity(denom.id, getItemQuantity(denom.id) + 1)"
                                            x-show="isItemSelected(denom.id)"
                                            class="w-8 h-8 bg-gray-700 hover:bg-gray-600 rounded-full flex items-center justify-center text-sm font-bold">
                                        +
                                    </button>
                                </div>
                            </label>
                        </template>
                    </div>
                </div>

                <!-- Add Discount -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium">Diskon (%)</label>
                        <div class="flex space-x-1">
                            <button @click="discount = 5; calculate()" 
                                    class="px-2 py-1 text-xs bg-gray-700 hover:bg-gray-600 rounded">5%</button>
                            <button @click="discount = 10; calculate()" 
                                    class="px-2 py-1 text-xs bg-gray-700 hover:bg-gray-600 rounded">10%</button>
                            <button @click="discount = 20; calculate()" 
                                    class="px-2 py-1 text-xs bg-gray-700 hover:bg-gray-600 rounded">20%</button>
                        </div>
                    </div>
                    <input type="number" 
                           x-model="discount"
                           @input="calculate()"
                           min="0" 
                           max="100"
                           placeholder="0"
                           class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none">
                </div>

                <!-- Add Fee -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium">Biaya Admin</label>
                        <div class="flex space-x-1">
                            <button @click="adminFee = 1000; calculate()" 
                                    class="px-2 py-1 text-xs bg-gray-700 hover:bg-gray-600 rounded">1K</button>
                            <button @click="adminFee = 2500; calculate()" 
                                    class="px-2 py-1 text-xs bg-gray-700 hover:bg-gray-600 rounded">2.5K</button>
                            <button @click="adminFee = 5000; calculate()" 
                                    class="px-2 py-1 text-xs bg-gray-700 hover:bg-gray-600 rounded">5K</button>
                        </div>
                    </div>
                    <input type="number" 
                           x-model="adminFee"
                           @input="calculate()"
                           min="0"
                           placeholder="0"
                           class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none">
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-2">
                    <button @click="calculate()" 
                            class="flex-1 py-3 bg-yellow-400 text-gray-900 rounded-lg font-semibold hover:bg-yellow-500 transition">
                        🧮 Hitung Total
                    </button>
                    <button @click="clearAll()" 
                            class="px-4 py-3 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition"
                            title="Clear all selections">
                        🗑️
                    </button>
                </div>

                <!-- Save/Load Buttons -->
                <div class="flex space-x-2">
                    <button @click="saveCalculation()" 
                            x-show="selectedItems.length > 0"
                            class="flex-1 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition">
                        💾 Simpan
                    </button>
                    <button @click="loadLastCalculation()" 
                            class="flex-1 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700 transition">
                        📂 Muat Terakhir
                    </button>
                </div>
            </div>
        </div>

        <!-- Result -->
        <div class="glass calculator-result calculator-content rounded-xl p-4 sm:p-6 mt-6 lg:mt-0">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg sm:text-xl font-semibold">Hasil Perhitungan</h2>
                <div class="text-sm text-gray-400" x-show="selectedItems.length > 0">
                    <span x-text="selectedItems.length"></span> item dipilih
                </div>
            </div>
            
            <div class="calculation-display">
                <!-- Empty State -->
                <div x-show="selectedItems.length === 0" class="text-center py-8">
                    <div class="text-6xl mb-4">🧮</div>
                    <h3 class="text-lg font-semibold text-gray-300 mb-2">Belum Ada Item</h3>
                    <p class="text-gray-400 text-sm">Pilih game dan item untuk mulai menghitung</p>
                </div>

                <!-- Calculation Results -->
                <div x-show="selectedItems.length > 0">
                    <!-- Summary Cards -->
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="bg-gray-800 p-4 rounded-lg text-center">
                            <div class="text-2xl font-bold text-white" x-text="formatCurrency(subtotal)"></div>
                            <div class="text-xs text-gray-400">Subtotal</div>
                        </div>
                        <div class="bg-yellow-400 bg-opacity-20 p-4 rounded-lg text-center border border-yellow-400">
                            <div class="text-2xl font-bold text-yellow-400" x-text="formatCurrency(total)"></div>
                            <div class="text-xs text-yellow-300">Total Bayar</div>
                        </div>
                    </div>

                    <!-- Detailed Breakdown -->
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-lg">
                            <span class="text-gray-300">Subtotal:</span>
                            <span x-text="formatCurrency(subtotal)"></span>
                        </div>
                        <div class="flex justify-between" x-show="discountAmount > 0">
                            <span class="text-gray-400">Diskon (<span x-text="discount"></span>%):</span>
                            <span class="text-green-400" x-text="'-' + formatCurrency(discountAmount)"></span>
                        </div>
                        <div class="flex justify-between" x-show="adminFee > 0">
                            <span class="text-gray-400">Biaya Admin:</span>
                            <span class="text-orange-400" x-text="formatCurrency(adminFee)"></span>
                        </div>
                        <div class="border-t border-gray-700 pt-3">
                            <div class="flex justify-between text-xl font-bold">
                                <span>Total Bayar:</span>
                                <span class="text-yellow-400" x-text="formatCurrency(total)"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Selected Items List -->
                    <div class="mb-6">
                        <h3 class="font-semibold mb-3 flex items-center">
                            <i class="w-4 h-4 mr-2">📋</i>
                            Detail Items:
                        </h3>
                        <div class="space-y-2 max-h-40 overflow-y-auto">
                            <template x-for="item in selectedItems" :key="item.id">
                                <div class="flex justify-between items-center p-3 bg-gray-800 rounded-lg">
                                    <div class="flex-1">
                                        <div class="font-medium text-sm" x-text="item.name"></div>
                                        <div class="text-xs text-gray-400">
                                            <span x-text="formatCurrency(item.price)"></span> × <span x-text="item.quantity"></span>
                                        </div>
                                    </div>
                                    <div class="font-semibold" x-text="formatCurrency(item.price * item.quantity)"></div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="grid grid-cols-2 gap-3 mb-6">
                        <button @click="copyToClipboard()" 
                                class="py-2 px-4 bg-blue-600 hover:bg-blue-700 rounded-lg text-sm transition">
                            📋 Copy Detail
                        </button>
                        <button onclick="window.print()" 
                                class="py-2 px-4 bg-green-600 hover:bg-green-700 rounded-lg text-sm transition">
                            🖨️ Print
                        </button>
                    </div>
                </div>

                <!-- Tips -->
                <div class="mt-6 p-4 bg-gradient-to-r from-yellow-400 bg-opacity-10 to-orange-500 bg-opacity-10 rounded-lg border border-yellow-400 border-opacity-30">
                    <h3 class="font-semibold text-yellow-400 mb-2 flex items-center">
                        <span class="mr-2">💡</span>
                        Tips Hemat Budget
                    </h3>
                    <ul class="text-sm text-gray-300 space-y-1">
                        <li>• Cari promo spesial di event bulanan</li>
                        <li>• Bundle purchase untuk diskon otomatis</li>
                        <li>• Gunakan loyalty points untuk potongan</li>
                        <li>• Follow social media untuk kode diskon</li>
                    </ul>
                </div>

                <!-- Payment Method Info -->
                <div class="mt-4 p-3 bg-gray-800 rounded-lg" x-show="total > 0">
                    <h4 class="font-medium text-sm mb-2">Estimasi Biaya Payment:</h4>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-gray-400">QRIS:</span>
                            <span x-text="formatCurrency(total + 1000)"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Bank Transfer:</span>
                            <span x-text="formatCurrency(total + 2500)"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function calculator() {
    return {
        selectedGame: '',
        denominations: [],
        selectedItems: [],
        subtotal: 0,
        discount: 0,
        discountAmount: 0,
        adminFee: 0,
        total: 0,
        
        init() {
            console.log('Calculator initialized');
            this.$watch('discount', () => this.calculate());
            this.$watch('adminFee', () => this.calculate());
        },
        
        updateDenominations() {
            console.log('Updating denominations for game:', this.selectedGame);
            const select = document.querySelector('select[x-model="selectedGame"]');
            const option = select.options[select.selectedIndex];
            
            if (option && option.value) {
                try {
                    this.denominations = JSON.parse(option.dataset.denominations || '[]');
                    console.log('Loaded denominations:', this.denominations);
                } catch (e) {
                    console.error('Error parsing denominations:', e);
                    this.denominations = [];
                }
            } else {
                this.denominations = [];
            }
            
            // Reset selected items when game changes
            this.selectedItems = [];
            this.resetCalculation();
        },
        
        toggleItem(denom) {
            console.log('Toggling item:', denom);
            const index = this.selectedItems.findIndex(item => item.id === denom.id);
            
            if (index > -1) {
                // Remove item
                this.selectedItems.splice(index, 1);
                // Uncheck the checkbox
                const checkbox = document.querySelector(`input[value="${denom.id}"]`);
                if (checkbox) checkbox.checked = false;
            } else {
                // Add item
                this.selectedItems.push({
                    id: denom.id,
                    name: denom.name,
                    price: parseFloat(denom.price),
                    quantity: 1
                });
                // Check the checkbox
                const checkbox = document.querySelector(`input[value="${denom.id}"]`);
                if (checkbox) checkbox.checked = true;
            }
            
            this.calculate();
        },
        
        updateQuantity(id, qty) {
            const item = this.selectedItems.find(item => item.id === id);
            if (item) {
                const quantity = Math.max(1, parseInt(qty) || 1);
                item.quantity = quantity;
                console.log('Updated quantity for item', id, 'to', quantity);
                this.calculate();
            }
        },
        
        calculate() {
            console.log('Calculating totals...');
            
            // Calculate subtotal
            this.subtotal = this.selectedItems.reduce((sum, item) => {
                return sum + (item.price * item.quantity);
            }, 0);
            
            // Calculate discount amount
            const discountPercent = Math.min(100, Math.max(0, parseFloat(this.discount) || 0));
            this.discountAmount = this.subtotal * (discountPercent / 100);
            
            // Calculate total
            const adminFee = Math.max(0, parseFloat(this.adminFee) || 0);
            this.total = Math.max(0, this.subtotal - this.discountAmount + adminFee);
            
            console.log('Calculation result:', {
                subtotal: this.subtotal,
                discount: discountPercent,
                discountAmount: this.discountAmount,
                adminFee: adminFee,
                total: this.total
            });
        },
        
        resetCalculation() {
            this.subtotal = 0;
            this.discountAmount = 0;
            this.total = 0;
        },
        
        formatCurrency(amount) {
            const value = parseFloat(amount) || 0;
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
        },
        
        // Helper methods
        isItemSelected(denomId) {
            return this.selectedItems.some(item => item.id === denomId);
        },
        
        getItemQuantity(denomId) {
            const item = this.selectedItems.find(item => item.id === denomId);
            return item ? item.quantity : 1;
        },
        
        // Clear all selections
        clearAll() {
            this.selectedItems = [];
            this.selectedGame = '';
            this.denominations = [];
            this.discount = 0;
            this.adminFee = 0;
            this.resetCalculation();
            
            // Uncheck all checkboxes
            document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
        },
        
        // Save calculation (localStorage)
        saveCalculation() {
            const calculation = {
                selectedGame: this.selectedGame,
                selectedItems: this.selectedItems,
                discount: this.discount,
                adminFee: this.adminFee,
                total: this.total,
                timestamp: Date.now()
            };
            
            localStorage.setItem('calculator_last', JSON.stringify(calculation));
            
            // Show notification
            this.showNotification('Perhitungan disimpan!', 'success');
        },
        
        // Load last calculation
        loadLastCalculation() {
            try {
                const saved = localStorage.getItem('calculator_last');
                if (saved) {
                    const calculation = JSON.parse(saved);
                    
                    this.selectedGame = calculation.selectedGame || '';
                    this.discount = calculation.discount || 0;
                    this.adminFee = calculation.adminFee || 0;
                    
                    // Trigger denomination update
                    if (this.selectedGame) {
                        this.updateDenominations();
                        
                        // Restore selected items after a short delay
                        setTimeout(() => {
                            this.selectedItems = calculation.selectedItems || [];
                            this.calculate();
                        }, 100);
                    }
                    
                    this.showNotification('Perhitungan terakhir dimuat!', 'info');
                }
            } catch (e) {
                console.error('Error loading last calculation:', e);
            }
        },
        
        showNotification(message, type = 'info') {
            // Simple notification
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transition-all duration-300 ${
                type === 'success' ? 'bg-green-600' : 
                type === 'error' ? 'bg-red-600' : 'bg-blue-600'
            } text-white`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    if (document.body.contains(notification)) {
                        document.body.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        },
        
        // Copy calculation to clipboard
        copyToClipboard() {
            const gameName = this.selectedGame ? 
                document.querySelector(`option[value="${this.selectedGame}"]`).textContent : 
                'Unknown Game';
                
            let text = `📋 KALKULASI TOP UP - TAKAPEDIA\n`;
            text += `━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n`;
            text += `🎮 Game: ${gameName}\n`;
            text += `📅 Tanggal: ${new Date().toLocaleDateString('id-ID')}\n\n`;
            
            text += `📦 DETAIL ITEMS:\n`;
            this.selectedItems.forEach((item, index) => {
                text += `${index + 1}. ${item.name}\n`;
                text += `   ${this.formatCurrency(item.price)} × ${item.quantity} = ${this.formatCurrency(item.price * item.quantity)}\n`;
            });
            
            text += `\n💰 RINGKASAN BIAYA:\n`;
            text += `Subtotal: ${this.formatCurrency(this.subtotal)}\n`;
            if (this.discountAmount > 0) {
                text += `Diskon (${this.discount}%): -${this.formatCurrency(this.discountAmount)}\n`;
            }
            if (this.adminFee > 0) {
                text += `Biaya Admin: ${this.formatCurrency(this.adminFee)}\n`;
            }
            text += `━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n`;
            text += `TOTAL BAYAR: ${this.formatCurrency(this.total)}\n`;
            text += `━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n`;
            text += `\n💡 Estimasi dengan biaya payment:\n`;
            text += `QRIS: ${this.formatCurrency(this.total + 1000)}\n`;
            text += `Bank Transfer: ${this.formatCurrency(this.total + 2500)}\n`;
            text += `\n🌐 Dibuat dengan Takapedia Calculator`;
            
            navigator.clipboard.writeText(text).then(() => {
                this.showNotification('Detail berhasil dicopy!', 'success');
            }).catch(() => {
                // Fallback for older browsers
                const textarea = document.createElement('textarea');
                textarea.value = text;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                this.showNotification('Detail berhasil dicopy!', 'success');
            });
        }
    }
}
</script>
@endpush