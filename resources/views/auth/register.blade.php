@extends('layouts.app')

@section('title', 'Daftar - Takapedia Clone')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4 sm:px-6 lg:px-8 py-8">
    <div class="w-full max-w-md">
        <!-- Logo & Title -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-yellow-400 neon-text mb-2">⚡ Takapedia</h1>
            <p class="text-gray-400">Buat akun baru</p>
        </div>
        
        <!-- Register Form -->
        <div class="glass rounded-xl p-8">
            <form method="POST" action="{{ route('register') }}" x-data="{ showPassword: false, showPasswordConfirm: false }">
                @csrf
                
                <!-- Name -->
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium mb-2">Nama Lengkap</label>
                    <div class="relative">
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}"
                               placeholder="John Doe"
                               class="w-full px-4 py-3 pl-10 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none transition"
                               required 
                               autofocus>
                        <i data-lucide="user" class="absolute left-3 top-3.5 w-5 h-5 text-gray-400"></i>
                    </div>
                    @error('name')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium mb-2">Email</label>
                    <div class="relative">
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}"
                               placeholder="example@gmail.com"
                               class="w-full px-4 py-3 pl-10 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none transition"
                               required>
                        <i data-lucide="mail" class="absolute left-3 top-3.5 w-5 h-5 text-gray-400"></i>
                    </div>
                    @error('email')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium mb-2">Password</label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" 
                               id="password" 
                               name="password"
                               placeholder="Minimal 8 karakter"
                               class="w-full px-4 py-3 pl-10 pr-10 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none transition"
                               required>
                        <i data-lucide="lock" class="absolute left-3 top-3.5 w-5 h-5 text-gray-400"></i>
                        <button type="button" 
                                @click="showPassword = !showPassword; setTimeout(() => lucide.createIcons(), 10)"
                                class="absolute right-3 top-3.5 text-gray-400 hover:text-gray-300">
                            <i :data-lucide="showPassword ? 'eye-off' : 'eye'" class="w-5 h-5"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Confirm Password -->
                <div class="mb-6">
                    <label for="password_confirmation" class="block text-sm font-medium mb-2">Konfirmasi Password</label>
                    <div class="relative">
                        <input :type="showPasswordConfirm ? 'text' : 'password'" 
                               id="password_confirmation" 
                               name="password_confirmation"
                               placeholder="Ulangi password"
                               class="w-full px-4 py-3 pl-10 pr-10 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none transition"
                               required>
                        <i data-lucide="lock" class="absolute left-3 top-3.5 w-5 h-5 text-gray-400"></i>
                        <button type="button" 
                                @click="showPasswordConfirm = !showPasswordConfirm; setTimeout(() => lucide.createIcons(), 10)"
                                class="absolute right-3 top-3.5 text-gray-400 hover:text-gray-300">
                            <i :data-lucide="showPasswordConfirm ? 'eye-off' : 'eye'" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Terms & Conditions -->
                <div class="mb-6">
                    <label class="flex items-start">
                        <input type="checkbox" 
                               name="terms" 
                               required
                               class="mt-1 rounded bg-gray-800 border-gray-700 text-yellow-400 focus:ring-yellow-400">
                        <span class="ml-2 text-sm text-gray-400">
                            Saya setuju dengan 
                            <a href="#" class="text-yellow-400 hover:underline">Syarat & Ketentuan</a> 
                            dan 
                            <a href="#" class="text-yellow-400 hover:underline">Kebijakan Privasi</a>
                        </span>
                    </label>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full py-3 bg-yellow-400 text-gray-900 rounded-lg font-semibold hover:bg-yellow-500 transition neon-glow">
                    <i data-lucide="user-plus" class="inline w-5 h-5 mr-2"></i>
                    Daftar Sekarang
                </button>
                
                <!-- Login Link -->
                <p class="text-center mt-6 text-gray-400">
                    Sudah punya akun? 
                    <a href="{{ route('login') }}" class="text-yellow-400 hover:underline font-semibold">
                        Masuk di sini
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
</script>
@endpush