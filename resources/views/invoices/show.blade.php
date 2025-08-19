@extends('layouts.app')

@section('title', 'Invoice ' . $order->invoice_no)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-gray-100">
            @if($order->status === 'PENDING' || $order->status === 'UNPAID')
                Harap lengkapi pembayaran.
            @elseif($order->status === 'PAID')
                Pembayaran Berhasil!
            @elseif($order->status === 'DELIVERED')
                Pesanan Selesai!
            @else
                Status Pesanan
            @endif
        </h1>
        <p class="text-gray-400 mt-2">
            Pesanan kamu <span class="font-semibold">{{ $order->invoice_no }}</span> 
            @if($order->canBePaid())
                menunggu pembayaran sebelum dikirm.
            @endif
        </p>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Left Column - Order Details -->
        <div class="glass rounded-xl p-6">
            <h2 class="text-lg font-semibold mb-4">Detail Pembelian</h2>
            
            <div class="space-y-3">
                <div>
                    <span class="text-gray-400 text-sm">Pembelian produk:</span>
                    <p class="font-semibold">{{ $order->game->name }} - {{ $order->denomination->name }}</p>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-gray-400 text-sm">Nomor Invoice</span>
                        <p class="font-mono">{{ $order->invoice_no }}</p>
                    </div>
                    
                    <div>
                        <span class="text-gray-400 text-sm">Status Transaksi</span>
                        <p>
                            <span class="px-2 py-1 rounded text-xs font-semibold
                                {{ $order->status === 'PAID' || $order->status === 'DELIVERED' ? 'bg-green-500' : '' }}
                                {{ $order->status === 'PENDING' || $order->status === 'UNPAID' ? 'bg-yellow-500' : '' }}
                                {{ $order->status === 'EXPIRED' || $order->status === 'FAILED' ? 'bg-red-500' : '' }}
                                text-white">
                                {{ $order->status }}
                            </span>
                        </p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-gray-400 text-sm">Status Pembayaran</span>
                        <p>
                            <span class="px-2 py-1 rounded text-xs font-semibold
                                {{ $order->payment && $order->payment->status === 'PAID' ? 'bg-green-500' : 'bg-red-500' }}
                                text-white">
                                {{ $order->payment ? $order->payment->status : 'UNPAID' }}
                            </span>
                        </p>
                    </div>
                    
                    <div>
                        <span class="text-gray-400 text-sm">Pesan</span>
                        <p class="text-sm">
                            @if($order->canBePaid())
                                Silakan melakukan pembayaran dengan metode yang kamu pilih.
                            @elseif($order->isPaid())
                                Pembayaran telah diterima, pesanan sedang diproses.
                            @elseif($order->status === 'DELIVERED')
                                Pesanan telah berhasil dikirim.
                            @else
                                Pesanan telah expired atau dibatalkan.
                            @endif
                        </p>
                    </div>
                </div>
                
                <div class="border-t border-gray-700 pt-3">
                    <h3 class="font-semibold mb-2">Rincian Pembayaran</h3>
                    
                    <a href="#" 
                       @click.prevent="document.getElementById('payment-details').classList.toggle('hidden')"
                       class="text-yellow-400 hover:underline text-sm">
                        Lihat Rincian Pembayaran ▼
                    </a>
                    
                    <div id="payment-details" class="hidden mt-3 space-y-2 text-sm">
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
                        <div class="flex justify-between font-semibold text-base pt-2 border-t border-gray-700">
                            <span>Total Pembayaran:</span>
                            <span class="text-yellow-400">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Download Invoice Button -->
            <a href="{{ route('invoices.download', $order->invoice_no) }}" 
               class="mt-6 w-full block text-center py-2 bg-yellow-400 text-gray-900 rounded-lg hover:bg-yellow-500 transition">
                Download Invoice
            </a>
        </div>
        
        <!-- Right Column - Payment Instructions -->
        <div class="glass rounded-xl p-6">
            @if($order->canBePaid())
                <div class="text-center mb-4">
                    <p class="text-sm text-gray-400">Pesanan ini akan kedaluwarsa pada</p>
                    <div class="text-2xl font-bold text-red-400" 
                         x-data="countdown('{{ $order->expires_at->toIso8601String() }}')"
                         x-text="timeLeft">
                        Loading...
                    </div>
                </div>
                
                <h3 class="text-lg font-semibold mb-4">Metode Pembayaran</h3>
                <p class="font-semibold">{{ $order->payment->method }} ({{ $order->payment->channel ?? 'All Payment' }})</p>
                
                <div class="mt-6">
                    <h4 class="font-semibold mb-3">Cara Melakukan Pembayaran</h4>
                    
                    @if($order->payment->method === 'QRIS' && $qrCode)
                    <div class="text-center">
                        <img src="data:image/png;base64,{{ $qrCode }}" 
                             alt="QR Code" 
                             class="mx-auto mb-4 rounded-lg">
                        
                        <button onclick="downloadQR()" 
                                class="px-4 py-2 bg-yellow-400 text-gray-900 rounded-lg hover:bg-yellow-500 transition">
                            Unduh Kode QR
                        </button>
                        
                        <div class="mt-4 text-left text-sm text-gray-400">
                            <ol class="list-decimal list-inside space-y-1">
                                <li>Buka aplikasi e-wallet atau mobile banking</li>
                                <li>Pilih menu Scan QR atau QRIS</li>
                                <li>Scan kode QR di atas</li>
                                <li>Periksa detail dan konfirmasi pembayaran</li>
                                <li>Simpan bukti pembayaran</li>
                            </ol>
                        </div>
                    </div>
                    
                    @elseif($order->payment->method === 'VA')
                    <div class="glass rounded-lg p-4 mb-4">
                        <p class="text-sm text-gray-400 mb-2">Nomor Virtual Account:</p>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xl">{{ $order->payment->va_number }}</span>
                            <button onclick="copyToClipboard('{{ $order->payment->va_number }}')" 
                                    class="px-3 py-1 bg-gray-700 rounded hover:bg-gray-600 transition">
                                <i data-lucide="copy" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="text-sm text-gray-400">
                        <p class="mb-2">Cara pembayaran melalui {{ strtoupper($order->payment->channel) }}:</p>
                        <ol class="list-decimal list-inside space-y-1">
                            <li>Buka aplikasi/ATM {{ strtoupper($order->payment->channel) }}</li>
                            <li>Pilih menu Transfer/Virtual Account</li>
                            <li>Masukkan nomor VA: {{ $order->payment->va_number }}</li>
                            <li>Masukkan nominal: Rp {{ number_format($order->total, 0, ',', '.') }}</li>
                            <li>Konfirmasi pembayaran</li>
                        </ol>
                    </div>
                    
                    @elseif($order->payment->method === 'EWALLET')
                    <div class="text-center">
                        <a href="{{ $order->payment->checkout_url }}" 
                           target="_blank"
                           class="inline-block px-6 py-3 bg-yellow-400 text-gray-900 rounded-lg hover:bg-yellow-500 transition">
                            Bayar dengan {{ ucfirst($order->payment->channel) }}
                        </a>
                        
                        <p class="mt-4 text-sm text-gray-400">
                            Klik tombol di atas untuk melanjutkan pembayaran melalui {{ ucfirst($order->payment->channel) }}
                        </p>
                    </div>
                    @endif
                </div>
            @elseif($order->status === 'PAID' || $order->status === 'DELIVERED')
                <div class="text-center py-8">
                    <div class="w-20 h-20 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="check-circle" class="w-10 h-10 text-white"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Pembayaran Berhasil!</h3>
                    <p class="text-gray-400">
                        @if($order->status === 'DELIVERED')
                            Pesanan kamu telah berhasil dikirim ke akun game.
                        @else
                            Pesanan kamu sedang diproses dan akan segera dikirim.
                        @endif
                    </p>
                    
                    @if($order->delivery_data)
                    <div class="mt-6 text-left glass rounded-lg p-4">
                        <h4 class="font-semibold mb-2">Data Pengiriman:</h4>
                        <pre class="text-sm text-gray-400">{{ $order->delivery_data }}</pre>
                    </div>
                    @endif
                </div>
            @else
                <div class="text-center py-8">
                    <div class="w-20 h-20 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="x-circle" class="w-10 h-10 text-white"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Pesanan Expired</h3>
                    <p class="text-gray-400">
                        Pesanan ini telah kedaluwarsa. Silakan buat pesanan baru.
                    </p>
                    
                    <a href="{{ route('games.show', $order->game->slug) }}" 
                       class="inline-block mt-4 px-6 py-2 bg-yellow-400 text-gray-900 rounded-lg hover:bg-yellow-500 transition">
                        Buat Pesanan Baru
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function countdown(expiresAt) {
    return {
        timeLeft: '',
        init() {
            this.updateCountdown();
            setInterval(() => this.updateCountdown(), 1000);
        },
        updateCountdown() {
            const now = new Date().getTime();
            const expires = new Date(expiresAt).getTime();
            const distance = expires - now;
            
            if (distance < 0) {
                this.timeLeft = 'EXPIRED';
                location.reload();
                return;
            }
            
            const hours = Math.floor(distance / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            this.timeLeft = `${hours} hours, ${minutes} minutes, ${seconds} seconds left`;
        }
    }
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Nomor VA berhasil disalin!');
    });
}

function downloadQR() {
    const link = document.createElement('a');
    link.download = 'qr-{{ $order->invoice_no }}.png';
    link.href = document.querySelector('img[alt="QR Code"]').src;
    link.click();
}

// Auto refresh status every 10 seconds if pending
@if($order->canBePaid())
setInterval(() => {
    fetch(window.location.href)
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newStatus = doc.querySelector('[class*="bg-green-500"]');
            if (newStatus) {
                location.reload();
            }
        });
}, 10000);
@endif
</script>
@endpush