@props(['order'])

<div class="glass rounded-lg p-4">
    <h4 class="font-semibold mb-3">Ringkasan Pembayaran</h4>
    
    <div class="space-y-2 text-sm">
        <div class="flex justify-between">
            <span class="text-gray-400">Subtotal:</span>
            <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
        </div>
        
        @if($order->discount > 0)
            <div class="flex justify-between">
                <span class="text-gray-400">Diskon:</span>
                <span class="text-green-400">-Rp {{ number_format($order->discount, 0, ',', '.') }}</span>
            </div>
        @endif
        
        <div class="flex justify-between">
            <span class="text-gray-400">Biaya Admin:</span>
            <span>Rp {{ number_format($order->fee, 0, ',', '.') }}</span>
        </div>
        
        <div class="border-t border-gray-700 pt-2">
            <div class="flex justify-between font-semibold text-base">
                <span>Total:</span>
                <span class="text-yellow-400">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
</div>