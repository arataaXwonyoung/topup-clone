@extends('layouts.app')

@section('title', 'Kalkulator - Hitung Budget Top Up')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="glass rounded-xl p-6 mb-8">
        <h1 class="text-3xl font-bold text-yellow-400 mb-4">Kalkulator Top Up</h1>
        <p class="text-gray-400">Hitung budget yang kamu butuhkan untuk top up game favoritmu!</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Calculator Form -->
        <div class="glass rounded-xl p-6">
            <h2 class="text-xl font-semibold mb-4">Pilih Game & Item</h2>
            
            <div x-data="calculator()" class="space-y-4">
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
                    <label class="block text-sm font-medium mb-2">Pilih Item</label>
                    <div class="space-y-2 max-h-60 overflow-y-auto">
                        <template x-for="denom in denominations" :key="denom.id">
                            <label class="flex items-center justify-between p-3 bg-gray-800 rounded-lg cursor-pointer hover:bg-gray-700">
                                <div class="flex items-center">
                                    <input type="checkbox" 
                                           :value="denom.id"
                                           @change="toggleItem(denom)"
                                           class="mr-3 text-yellow-400 focus:ring-yellow-400">
                                    <div>
                                        <span x-text="denom.name"></span>
                                        <div class="text-xs text-gray-400" x-text="formatCurrency(denom.price)"></div>
                                    </div>
                                </div>
                                <input type="number" 
                                       :id="'qty-' + denom.id"
                                       @input="updateQuantity(denom.id, $event.target.value)"
                                       min="1" 
                                       value="1"
                                       class="w-16 px-2 py-1 bg-gray-700 rounded text-center">
                            </label>
                        </template>
                    </div>
                </div>

                <!-- Add Discount -->
                <div>
                    <label class="block text-sm font-medium mb-2">Diskon (%)</label>
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
                    <label class="block text-sm font-medium mb-2">Biaya Admin</label>
                    <input type="number" 
                           x-model="adminFee"
                           @input="calculate()"
                           min="0"
                           placeholder="0"
                           class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none">
                </div>

                <button @click="calculate()" 
                        class="w-full py-3 bg-yellow-400 text-gray-900 rounded-lg font-semibold hover:bg-yellow-500 transition">
                    Hitung Total
                </button>
            </div>
        </div>

        <!-- Result -->
        <div class="glass rounded-xl p-6">
            <h2 class="text-xl font-semibold mb-4">Hasil Perhitungan</h2>
            
            <div x-data="calculator()">
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Subtotal:</span>
                        <span x-text="formatCurrency(subtotal)"></span>
                    </div>
                    <div class="flex justify-between" x-show="discountAmount > 0">
                        <span class="text-gray-400">Diskon:</span>
                        <span class="text-green-400" x-text="'-' + formatCurrency(discountAmount)"></span>
                    </div>
                    <div class="flex justify-between" x-show="adminFee > 0">
                        <span class="text-gray-400">Biaya Admin:</span>
                        <span x-text="formatCurrency(adminFee)"></span>
                    </div>
                    <div class="border-t border-gray-700 pt-3">
                        <div class="flex justify-between text-xl font-bold">
                            <span>Total:</span>
                            <span class="text-yellow-400" x-text="formatCurrency(total)"></span>
                        </div>
                    </div>
                </div>

                <!-- Selected Items List -->
                <div class="mt-6" x-show="selectedItems.length > 0">
                    <h3 class="font-semibold mb-3">Item yang Dipilih:</h3>
                    <div class="space-y-2">
                        <template x-for="item in selectedItems" :key="item.id">
                            <div class="flex justify-between text-sm">
                                <span x-text="item.name + ' x' + item.quantity"></span>
                                <span x-text="formatCurrency(item.price * item.quantity)"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Tips -->
                <div class="mt-6 p-4 bg-gray-800 rounded-lg">
                    <h3 class="font-semibold text-yellow-400 mb-2">💡 Tips Hemat</h3>
                    <ul class="text-sm text-gray-400 space-y-1">
                        <li>• Beli dalam jumlah besar untuk diskon lebih</li>
                        <li>• Gunakan kode promo saat checkout</li>
                        <li>• Pantau event promo di halaman artikel</li>
                        <li>• Gabung member untuk bonus poin</li>
                    </ul>
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
        
        updateDenominations() {
            const select = document.querySelector('select[x-model="selectedGame"]');
            const option = select.options[select.selectedIndex];
            if (option.value) {
                this.denominations = JSON.parse(option.dataset.denominations || '[]');
            } else {
                this.denominations = [];
            }
            this.selectedItems = [];
            this.calculate();
        },
        
        toggleItem(denom) {
            const index = this.selectedItems.findIndex(item => item.id === denom.id);
            if (index > -1) {
                this.selectedItems.splice(index, 1);
            } else {
                this.selectedItems.push({
                    ...denom,
                    quantity: 1
                });
            }
            this.calculate();
        },
        
        updateQuantity(id, qty) {
            const item = this.selectedItems.find(item => item.id === id);
            if (item) {
                item.quantity = parseInt(qty) || 1;
                this.calculate();
            }
        },
        
        calculate() {
            this.subtotal = this.selectedItems.reduce((sum, item) => {
                return sum + (item.price * item.quantity);
            }, 0);
            
            this.discountAmount = this.subtotal * (this.discount / 100);
            this.total = this.subtotal - this.discountAmount + parseFloat(this.adminFee || 0);
        },
        
        formatCurrency(amount) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount || 0);
        }
    }
}
</script>
@endpush