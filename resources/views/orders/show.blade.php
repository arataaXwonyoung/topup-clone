@extends('layouts.app')

@section('title', 'Detail Order - ' . $order->invoice_no)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('orders.history') }}" 
           class="inline-flex items-center text-gray-400 hover:text-yellow-400 transition">
            <i data-lucide="arrow-left" class="w-5 h-5 mr-2"></i>
            Kembali ke Riwayat Transaksi
        </a>
    </div>

    <!-- Order Status Banner -->
    <div class="glass rounded-xl p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-yellow-400 mb-2">Detail Pesanan</h1>
                <p class="text-gray-400">Invoice: <span class="font-mono text-white">{{ $order->invoice_no }}</span></p>
            </div>
            <div class="mt-4 md:mt-0">
                @php
                    $statusColors = [
                        'PENDING' => 'bg-yellow-500',
                        'UNPAID' => 'bg-orange-500',
                        'PAID' => 'bg-blue-500',
                        'DELIVERED' => 'bg-green-500',
                        'EXPIRED' => 'bg-gray-500',
                        'FAILED' => 'bg-red-500',
                        'REFUNDED' => 'bg-purple-500',
                    ];
                    $statusLabels = [
                        'PENDING' => 'Menunggu',
                        'UNPAID' => 'Belum Bayar',
                        'PAID' => 'Dibayar',
                        'DELIVERED' => 'Selesai',
                        'EXPIRED' => 'Kadaluarsa',
                        'FAILED' => 'Gagal',
                        'REFUNDED' => 'Refund',
                    ];
                    $statusIcons = [
                        'PENDING' => 'clock',
                        'UNPAID' => 'alert-circle',
                        'PAID' => 'credit-card',
                        'DELIVERED' => 'check-circle',
                        'EXPIRED' => 'x-circle',
                        'FAILED' => 'x-octagon',
                        'REFUNDED' => 'rotate-ccw',
                    ];
                @endphp
                <div class="flex items-center space-x-2">
                    <i data-lucide="{{ $statusIcons[$order->status] ?? 'info' }}" 
                       class="w-6 h-6 {{ str_replace('bg-', 'text-', $statusColors[$order->status] ?? 'text-gray-500') }}"></i>
                    <span class="px-4 py-2 text-sm font-semibold rounded-full text-white {{ $statusColors[$order->status] ?? 'bg-gray-500' }}">
                        {{ $statusLabels[$order->status] ?? $order->status }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Product Details -->
            <div class="glass rounded-xl p-6">
                <h2 class="text-lg font-semibold mb-4 flex items-center">
                    <i data-lucide="package" class="w-5 h-5 mr-2 text-yellow-400"></i>
                    Detail Produk
                </h2>
                
                <div class="flex items-start space-x-4">
                    <img src="{{ $order->game->cover_path }}" 
                         alt="{{ $order->game->name }}"
                         class="w-20 h-20 rounded-lg">
                    <div class="flex-1">
                        <h3 class="font-semibold text-lg text-white">{{ $order->game->name }}</h3>
                        <p class="text-gray-400 text-sm mb-2">{{ $order->game->publisher }}</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
                            <div>
                                <span class="text-gray-400 text-sm">Item:</span>
                                <p class="font-semibold text-yellow-400">{{ $order->denomination->name }}</p>
                            </div>
                            <div>
                                <span class="text-gray-400 text-sm">Jumlah:</span>
                                <p class="font-semibold">{{ $order->quantity }}x</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Information -->
            <div class="glass rounded-xl p-6">
                <h2 class="text-lg font-semibold mb-4 flex items-center">
                    <i data-lucide="user" class="w-5 h-5 mr-2 text-yellow-400"></i>
                    Informasi Akun
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-gray-400 text-sm">{{ $order->game->id_label ?? 'User ID' }}:</span>
                        <p class="font-mono text-lg">{{ $order->account_id }}</p>
                    </div>
                    @if($order->server_id)
                    <div>
                        <span class="text-gray-400 text-sm">{{ $order->game->server_label ?? 'Server' }}:</span>
                        <p class="font-mono text-lg">{{ $order->server_id }}</p>
                    </div>
                    @endif
                    @if($order->username)
                    <div>
                        <span class="text-gray-400 text-sm">Username:</span>
                        <p class="font-semibold">{{ $order->username }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Payment Information -->
            <div class="glass rounded-xl p-6">
                <h2 class="text-lg font-semibold mb-4 flex items-center">
                    <i data-lucide="credit-card" class="w-5 h-5 mr-2 text-yellow-400"></i>
                    Informasi Pembayaran
                </h2>
                
                @if($order->payment)
                <div class="space-y-3">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <span class="text-gray-400 text-sm">Metode Pembayaran:</span>
                            <p class="font-semibold">{{ $order->payment->method }} 
                                @if($order->payment->channel)
                                    ({{ strtoupper($order->payment->channel) }})
                                @endif
                            </p>
                        </div>
                        <div>
                            <span class="text-gray-400 text-sm">Status Pembayaran:</span>
                            <p>
                                <span class="px-2 py-1 text-xs font-semibold rounded 
                                    {{ $order->payment->status === 'PAID' ? 'bg-green-500' : 'bg-red-500' }} text-white">
                                    {{ $order->payment->status }}
                                </span>
                            </p>
                        </div>
                    </div>
                    
                    @if($order->payment->reference)
                    <div>
                        <span class="text-gray-400 text-sm">Referensi Pembayaran:</span>
                        <p class="font-mono">{{ $order->payment->reference }}</p>
                    </div>
                    @endif
                    
                    @if($order->payment->va_number)
                    <div>
                        <span class="text-gray-400 text-sm">Nomor Virtual Account:</span>
                        <div class="flex items-center space-x-2">
                            <p class="font-mono text-lg">{{ $order->payment->va_number }}</p>
                            <button onclick="copyToClipboard('{{ $order->payment->va_number }}')" 
                                    class="text-yellow-400 hover:text-yellow-300">
                                <i data-lucide="copy" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                    @endif
                    
                    @if($order->paid_at)
                    <div>
                        <span class="text-gray-400 text-sm">Dibayar pada:</span>
                        <p>{{ $order->paid_at->format('d F Y H:i:s') }}</p>
                    </div>
                    @endif
                </div>
                @else
                <p class="text-gray-400">Belum ada informasi pembayaran</p>
                @endif
            </div>

            <!-- Delivery Information (if delivered) -->
            @if($order->status === 'DELIVERED')
            <div class="glass rounded-xl p-6">
                <h2 class="text-lg font-semibold mb-4 flex items-center">
                    <i data-lucide="truck" class="w-5 h-5 mr-2 text-yellow-400"></i>
                    Informasi Pengiriman
                </h2>
                
                <div class="space-y-3">
                    <div>
                        <span class="text-gray-400 text-sm">Status Pengiriman:</span>
                        <p class="text-green-400 font-semibold">
                            <i data-lucide="check-circle" class="inline w-4 h-4 mr-1"></i>
                            Berhasil Dikirim
                        </p>
                    </div>
                    
                    @if($order->delivered_at)
                    <div>
                        <span class="text-gray-400 text-sm">Dikirim pada:</span>
                        <p>{{ $order->delivered_at->format('d F Y H:i:s') }}</p>
                    </div>
                    @endif
                    
                    @if($order->delivery_data)
                    <div>
                        <span class="text-gray-400 text-sm">Data Pengiriman:</span>
                        <div class="mt-2 p-3 bg-gray-800 rounded-lg">
                            <pre class="text-xs text-gray-300 whitespace-pre-wrap">{{ $order->delivery_data }}</pre>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Review Section -->
            @if($order->status === 'DELIVERED')
                @if($order->review)
                <div class="glass rounded-xl p-6">
                    <h2 class="text-lg font-semibold mb-4 flex items-center">
                        <i data-lucide="star" class="w-5 h-5 mr-2 text-yellow-400"></i>
                        Review Anda
                    </h2>
                    
                    <div>
                        <div class="flex items-center mb-3">
                            <div class="flex text-yellow-400">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $order->review->rating)
                                        <i data-lucide="star" class="w-5 h-5 fill-current"></i>
                                    @else
                                        <i data-lucide="star" class="w-5 h-5"></i>
                                    @endif
                                @endfor
                            </div>
                            <span class="ml-3 text-sm text-gray-400">
                                {{ $order->review->created_at->format('d F Y') }}
                            </span>
                        </div>
                        
                        @if($order->review->comment)
                        <p class="text-gray-300">{{ $order->review->comment }}</p>
                        @endif
                        
                        @if(!$order->review->is_approved)
                        <p class="mt-2 text-sm text-yellow-400">
                            <i data-lucide="info" class="inline w-4 h-4 mr-1"></i>
                            Review sedang menunggu persetujuan admin
                        </p>
                        @endif
                    </div>
                </div>
                @else
                <div class="glass rounded-xl p-6">
                    <h2 class="text-lg font-semibold mb-4 flex items-center">
                        <i data-lucide="star" class="w-5 h-5 mr-2 text-yellow-400"></i>
                        Berikan Review
                    </h2>
                    
                    <p class="text-gray-400 mb-4">Bagikan pengalaman Anda untuk membantu pengguna lain</p>
                    
                    <form method="POST" action="{{ route('orders.review', $order->invoice_no) }}">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-300 mb-2">Rating</label>
                            <div class="flex space-x-2" x-data="{ rating: 0 }">
                                @for($i = 1; $i <= 5; $i++)
                                <button type="button"
                                        @click="rating = {{ $i }}"
                                        class="text-3xl transition">
                                    <i data-lucide="star" 
                                       :class="rating >= {{ $i }} ? 'text-yellow-400 fill-current' : 'text-gray-600'"></i>
                                </button>
                                @endfor
                                <input type="hidden" name="rating" x-model="rating" required>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-300 mb-2">Komentar (Opsional)</label>
                            <textarea name="comment" 
                                      rows="3"
                                      class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none"
                                      placeholder="Bagikan pengalaman Anda..."></textarea>
                        </div>
                        
                        <button type="submit" 
                                class="px-6 py-2 bg-yellow-400 text-gray-900 rounded-lg font-semibold hover:bg-yellow-500 transition">
                            <i data-lucide="send" class="inline w-4 h-4 mr-2"></i>
                            Kirim Review
                        </button>
                    </form>
                </div>
                @endif
            @endif
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Price Summary -->
            <div class="glass rounded-xl p-6">
                <h2 class="text-lg font-semibold mb-4 flex items-center">
                    <i data-lucide="receipt" class="w-5 h-5 mr-2 text-yellow-400"></i>
                    Ringkasan Harga
                </h2>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Subtotal:</span>
                        <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                    </div>
                    
                    @if($order->discount > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-400">Diskon:</span>
                        <span class="text-green-400">-Rp {{ number_format($order->discount, 0, ',', '.') }}</span>
                    </div>
                    @if($order->promo_code)
                    <div class="flex justify-between">
                        <span class="text-gray-400 text-sm">Kode Promo:</span>
                        <span class="text-sm font-mono">{{ $order->promo_code }}</span>
                    </div>
                    @endif
                    @endif
                    
                    <div class="flex justify-between">
                        <span class="text-gray-400">Biaya Admin:</span>
                        <span>Rp {{ number_format($order->fee, 0, ',', '.') }}</span>
                    </div>
                    
                    <div class="pt-3 border-t border-gray-700">
                        <div class="flex justify-between text-lg font-semibold">
                            <span>Total:</span>
                            <span class="text-yellow-400">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="glass rounded-xl p-6">
                <h2 class="text-lg font-semibold mb-4 flex items-center">
                    <i data-lucide="phone" class="w-5 h-5 mr-2 text-yellow-400"></i>
                    Kontak
                </h2>
                
                <div class="space-y-3">
                    <div>
                        <span class="text-gray-400 text-sm">Email:</span>
                        <p class="break-all">{{ $order->email }}</p>
                    </div>
                    <div>
                        <span class="text-gray-400 text-sm">WhatsApp:</span>
                        <p>{{ $order->whatsapp }}</p>
                    </div>
                </div>
            </div>

            <!-- Order Timeline -->
            <div class="glass rounded-xl p-6">
                <h2 class="text-lg font-semibold mb-4 flex items-center">
                    <i data-lucide="activity" class="w-5 h-5 mr-2 text-yellow-400"></i>
                    Timeline
                </h2>
                
                <div class="space-y-3">
                    <!-- Created -->
                    <div class="flex items-start space-x-3">
                        <div class="w-2 h-2 bg-gray-400 rounded-full mt-2"></div>
                        <div class="flex-1">
                            <p class="text-sm font-medium">Pesanan Dibuat</p>
                            <p class="text-xs text-gray-400">{{ $order->created_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                    
                    <!-- Paid -->
                    @if($order->paid_at)
                    <div class="flex items-start space-x-3">
                        <div class="w-2 h-2 bg-blue-400 rounded-full mt-2"></div>
                        <div class="flex-1">
                            <p class="text-sm font-medium">Pembayaran Diterima</p>
                            <p class="text-xs text-gray-400">{{ $order->paid_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Delivered -->
                    @if($order->delivered_at)
                    <div class="flex items-start space-x-3">
                        <div class="w-2 h-2 bg-green-400 rounded-full mt-2"></div>
                        <div class="flex-1">
                            <p class="text-sm font-medium">Pesanan Selesai</p>
                            <p class="text-xs text-gray-400">{{ $order->delivered_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Expired -->
                    @if($order->status === 'EXPIRED')
                    <div class="flex items-start space-x-3">
                        <div class="w-2 h-2 bg-red-400 rounded-full mt-2"></div>
                        <div class="flex-1">
                            <p class="text-sm font-medium">Pesanan Kadaluarsa</p>
                            <p class="text-xs text-gray-400">{{ $order->expires_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3">
                @if($order->canBePaid())
                <a href="{{ route('invoices.show', $order->invoice_no) }}" 
                   class="w-full block text-center py-3 bg-yellow-400 text-gray-900 rounded-lg font-semibold hover:bg-yellow-500 transition">
                    <i data-lucide="credit-card" class="inline w-5 h-5 mr-2"></i>
                    Bayar Sekarang
                </a>
                @endif
                
                <a href="{{ route('invoices.download', $order->invoice_no) }}" 
                   class="w-full block text-center py-3 bg-gray-700 text-white rounded-lg font-semibold hover:bg-gray-600 transition">
                    <i data-lucide="download" class="inline w-5 h-5 mr-2"></i>
                    Download Invoice
                </a>
                
                @if($order->status === 'DELIVERED')
                <a href="{{ route('orders.reorder', $order->invoice_no) }}" 
                   class="w-full block text-center py-3 border border-yellow-400 text-yellow-400 rounded-lg font-semibold hover:bg-yellow-400 hover:text-gray-900 transition">
                    <i data-lucide="refresh-cw" class="inline w-5 h-5 mr-2"></i>
                    Pesan Lagi
                </a>
                @endif
                
                @if($order->canBePaid())
                <form method="POST" action="{{ route('orders.cancel', $order->invoice_no) }}" 
                      onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="w-full py-3 border border-red-400 text-red-400 rounded-lg font-semibold hover:bg-red-400 hover:text-gray-900 transition">
                        <i data-lucide="x-circle" class="inline w-5 h-5 mr-2"></i>
                        Batalkan Pesanan
                    </button>
                </form>
                @endif
            </div>

            <!-- Need Help -->
            <div class="glass rounded-xl p-6 text-center">
                <i data-lucide="help-circle" class="w-12 h-12 text-yellow-400 mx-auto mb-3"></i>
                <h3 class="font-semibold mb-2">Butuh Bantuan?</h3>
                <p class="text-sm text-gray-400 mb-4">Tim support kami siap membantu Anda</p>
                <a href="https://wa.me/6281234567890?text=Halo,%20saya%20butuh%20bantuan%20untuk%20order%20{{ $order->invoice_no }}" 
                   target="_blank"
                   class="inline-block px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition">
                    <i data-lucide="message-circle" class="inline w-4 h-4 mr-2"></i>
                    WhatsApp Support
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Initialize Lucide icons
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            // Show toast notification
            const toast = document.createElement('div');
            toast.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
            toast.textContent = 'Berhasil disalin!';
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        });
    }
</script>
@endpush