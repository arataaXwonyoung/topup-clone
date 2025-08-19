@extends('layouts.app')

@section('title', 'Lupa Password - Takapedia Clone')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <!-- Logo & Title -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-yellow-400 neon-text mb-2">⚡ Takapedia</h1>
            <h2 class="text-2xl font-semibold text-gray-100">Lupa Password?</h2>
            <p class="mt-2 text-gray-400">
                Tidak masalah, kami akan mengirimkan link reset password ke email Anda.
            </p>
        </div>

        <!-- Forgot Password Form -->
        <div class="glass rounded-xl p-8">
            <!-- Session Status -->
            @if (session('status'))
                <div class="mb-4 p-4 rounded-lg bg-green-500/20 border border-green-500 text-green-400">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <!-- Info Text -->
                <div class="mb-6 p-4 rounded-lg bg-blue-500/10 border border-blue-500/30">
                    <p class="text-sm text-blue-400">
                        <i data-lucide="info" class="inline w-4 h-4 mr-1"></i>
                        Masukkan alamat email yang terdaftar di akun Anda. Kami akan mengirimkan link untuk mengatur ulang password.
                    </p>
                </div>

                <!-- Email Address -->
                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-2">
                        Email Terdaftar
                    </label>
                    <div class="relative">
                        <input id="email" 
                               type="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required 
                               autofocus 
                               autocomplete="username"
                               class="w-full px-4 py-3 pl-10 bg-gray-800 border border-gray-700 rounded-lg focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400/20 text-gray-100"
                               placeholder="email@example.com">
                        <i data-lucide="mail" class="absolute left-3 top-3.5 w-5 h-5 text-gray-500"></i>
                    </div>
                    @error('email')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full py-3 px-4 bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-semibold rounded-lg transition duration-200 neon-glow mb-4">
                    <i data-lucide="send" class="inline w-5 h-5 mr-2"></i>
                    Kirim Link Reset Password
                </button>

                <!-- Back to Login -->
                <a href="{{ route('login') }}" 
                   class="block w-full text-center py-3 px-4 border border-gray-700 hover:bg-gray-800 text-gray-300 font-medium rounded-lg transition duration-200">
                    <i data-lucide="arrow-left" class="inline w-5 h-5 mr-2"></i>
                    Kembali ke Halaman Login
                </a>
            </form>
        </div>

        <!-- Help Text -->
        <div class="mt-8 text-center">
            <p class="text-sm text-gray-500">
                Tidak menerima email? 
                <button type="button" 
                        onclick="document.querySelector('form').submit()"
                        class="text-yellow-400 hover:text-yellow-300">
                    Kirim ulang
                </button>
            </p>
            <p class="text-sm text-gray-500 mt-2">
                Butuh bantuan? 
                <a href="#" class="text-yellow-400 hover:text-yellow-300">
                    Hubungi support
                </a>
            </p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Initialize Lucide icons
    lucide.createIcons();
</script>
@endpush