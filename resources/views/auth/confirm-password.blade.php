@extends('layouts.app')

@section('title', 'Konfirmasi Password')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <div class="glass rounded-xl p-8">
            <!-- Logo & Title -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-yellow-400 neon-text mb-2">⚡ Takapedia</h1>
                <h2 class="text-xl font-semibold text-gray-100">Konfirmasi Password</h2>
                <p class="mt-2 text-sm text-gray-400">
                    Ini adalah area aman aplikasi. Harap konfirmasi password Anda sebelum melanjutkan.
                </p>
            </div>

            <!-- Session Status -->
            @if (session('status'))
                <div class="mb-4 p-4 rounded-lg bg-green-500/20 border border-green-500 text-green-400">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Form -->
            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf

                <!-- Password -->
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
                        Password
                    </label>
                    <div class="relative">
                        <input type="password"
                               id="password"
                               name="password"
                               required
                               autocomplete="current-password"
                               class="w-full px-4 py-3 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400/20 transition"
                               placeholder="Masukkan password Anda">
                        <i data-lucide="lock" class="absolute right-3 top-3.5 w-5 h-5 text-gray-400"></i>
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit"
                            class="w-full py-3 bg-yellow-400 text-gray-900 rounded-lg font-semibold hover:bg-yellow-500 transform hover:scale-[1.02] transition-all duration-200 neon-glow">
                        Konfirmasi
                    </button>
                </div>

                <!-- Forgot Password Link -->
                @if (Route::has('password.request'))
                    <div class="mt-6 text-center">
                        <a href="{{ route('password.request') }}" 
                           class="text-sm text-yellow-400 hover:text-yellow-300 transition">
                            Lupa password?
                        </a>
                    </div>
                @endif
            </form>
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
</script>
@endsection