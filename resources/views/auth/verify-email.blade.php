@extends('layouts.app')

@section('title', 'Verifikasi Email')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <div class="glass rounded-xl p-8">
            <!-- Logo & Title -->
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-yellow-400/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="mail" class="w-10 h-10 text-yellow-400"></i>
                </div>
                <h1 class="text-3xl font-bold text-yellow-400 neon-text mb-2">⚡ Takapedia</h1>
                <h2 class="text-xl font-semibold text-gray-100">Verifikasi Email Anda</h2>
            </div>

            <!-- Success Message -->
            @if (session('resent'))
                <div class="mb-6 p-4 rounded-lg bg-green-500/20 border border-green-500">
                    <div class="flex items-start">
                        <i data-lucide="check-circle" class="w-5 h-5 text-green-400 mr-2 flex-shrink-0 mt-0.5"></i>
                        <p class="text-green-400 text-sm">
                            Link verifikasi baru telah dikirim ke alamat email Anda.
                        </p>
                    </div>
                </div>
            @endif

            <!-- Instructions -->
            <div class="mb-6">
                <p class="text-gray-300 text-center mb-4">
                    Terima kasih telah mendaftar! Sebelum memulai, mohon verifikasi alamat email Anda dengan mengklik link yang telah kami kirimkan ke email Anda.
                </p>
                <p class="text-gray-400 text-sm text-center">
                    Jika Anda tidak menerima email tersebut, kami dengan senang hati akan mengirimkan ulang.
                </p>
            </div>

            <!-- Email Display -->
            <div class="mb-6 p-4 rounded-lg bg-gray-800 border border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Email terdaftar:</p>
                        <p class="text-yellow-400 font-medium">{{ Auth::user()->email }}</p>
                    </div>
                    <i data-lucide="mail-check" class="w-8 h-8 text-gray-600"></i>
                </div>
            </div>

            <!-- Actions -->
            <div class="space-y-3">
                <!-- Resend Verification Email -->
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit"
                            class="w-full py-3 bg-yellow-400 text-gray-900 rounded-lg font-semibold hover:bg-yellow-500 transform hover:scale-[1.02] transition-all duration-200 neon-glow">
                        <i data-lucide="send" class="inline w-5 h-5 mr-2"></i>
                        Kirim Ulang Email Verifikasi
                    </button>
                </form>

                <!-- Logout -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full py-3 border border-gray-600 text-gray-300 rounded-lg font-medium hover:bg-gray-800 transition-all duration-200">
                        Logout
                    </button>
                </form>
            </div>

            <!-- Help Text -->
            <div class="mt-8 p-4 rounded-lg bg-blue-500/10 border border-blue-500/30">
                <div class="flex items-start">
                    <i data-lucide="info" class="w-5 h-5 text-blue-400 mr-2 flex-shrink-0 mt-0.5"></i>
                    <div>
                        <p class="text-blue-400 text-sm font-medium mb-1">Tips:</p>
                        <ul class="text-blue-300 text-xs space-y-1">
                            <li>• Cek folder spam/junk email Anda</li>
                            <li>• Pastikan email {{ Auth::user()->email }} sudah benar</li>
                            <li>• Email verifikasi berlaku selama 60 menit</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Info -->
        <div class="mt-6 text-center">
            <p class="text-gray-400 text-sm">
                Butuh bantuan? 
                <a href="#" class="text-yellow-400 hover:text-yellow-300 transition">
                    Hubungi Support
                </a>
            </p>
        </div>
    </div>
</div>

<script>
    // Initialize Lucide icons
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });

    // Auto refresh to check verification status
    setTimeout(function() {
        window.location.reload();
    }, 60000); // Refresh every 60 seconds
</script>
@endsection