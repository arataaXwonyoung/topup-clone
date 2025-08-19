@extends('layouts.app')

@section('title', 'Login - Takapedia Clone')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md">
        <!-- Logo & Title -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-yellow-400 neon-text mb-2">⚡ Takapedia</h1>
            <p class="text-gray-400">Masuk ke akun Anda</p>
        </div>
        
        <!-- Login Form -->
        <div class="glass rounded-xl p-8">
            <form method="POST" action="{{ route('login') }}" x-data="{ showPassword: false }">
                @csrf
                
                <!-- Email -->
                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium mb-2">Email</label>
                    <div class="relative">
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}"
                               placeholder="example@gmail.com"
                               class="w-full px-4 py-3 pl-10 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none transition"
                               required 
                               autofocus>
                        <i data-lucide="mail" class="absolute left-3 top-3.5 w-5 h-5 text-gray-400"></i>
                    </div>
                    @error('email')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Password -->
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium mb-2">Password</label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" 
                               id="password" 
                               name="password"
                               placeholder="Masukkan password"
                               class="w-full px-4 py-3 pl-10 pr-10 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none transition"
                               required>
                        <i data-lucide="lock" class="absolute left-3 top-3.5 w-5 h-5 text-gray-400"></i>
                        <button type="button" 
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-3.5 text-gray-400 hover:text-gray-300">
                            <i :data-lucide="showPassword ? 'eye-off' : 'eye'" class="w-5 h-5"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="remember" 
                               class="rounded bg-gray-800 border-gray-700 text-yellow-400 focus:ring-yellow-400">
                        <span class="ml-2 text-sm text-gray-400">Ingat saya</span>
                    </label>
                    
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" 
                           class="text-sm text-yellow-400 hover:underline">
                            Lupa password?
                        </a>
                    @endif
                </div>
                
                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full py-3 bg-yellow-400 text-gray-900 rounded-lg font-semibold hover:bg-yellow-500 transition neon-glow">
                    <i data-lucide="log-in" class="inline w-5 h-5 mr-2"></i>
                    Masuk
                </button>
                
                <!-- Divider -->
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-700"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-gray-900 text-gray-400">Atau</span>
                    </div>
                </div>
                
                <!-- Social Login (Optional) -->
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" 
                            class="flex items-center justify-center px-4 py-2 border border-gray-700 rounded-lg hover:bg-gray-800 transition">
                        <img src="/images/google-icon.svg" alt="Google" class="w-5 h-5 mr-2">
                        Google
                    </button>
                    <button type="button" 
                            class="flex items-center justify-center px-4 py-2 border border-gray-700 rounded-lg hover:bg-gray-800 transition">
                        <i data-lucide="facebook" class="w-5 h-5 mr-2"></i>
                        Facebook
                    </button>
                </div>
                
                <!-- Register Link -->
                <p class="text-center mt-6 text-gray-400">
                    Belum punya akun? 
                    <a href="{{ route('register') }}" class="text-yellow-400 hover:underline font-semibold">
                        Daftar sekarang
                    </a>
                </p>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    lucide.createIcons();
    
    // Re-render icons when Alpine toggles password visibility
    document.addEventListener('alpine:init', () => {
        Alpine.data('login', () => ({
            showPassword: false,
            init() {
                this.$watch('showPassword', () => {
                    setTimeout(() => lucide.createIcons(), 10);
                });
            }
        }));
    });
</script>
@endpush