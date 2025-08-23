@extends('layouts.app')

@section('title', 'Login - Takapedia Clone')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md">

        {{-- Back to Home --}}
        <div class="mb-4">
            <a href="{{ url('/') }}" class="inline-flex items-center text-sm text-gray-400 hover:text-gray-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="mr-1 h-4 w-4" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali ke Beranda
            </a>
        </div>

        <!-- Logo & Title -->
        <div class="text-center mb-8">
            <h1 class="neon-text mb-2 text-4xl font-bold text-yellow-400">⚡ Takapedia</h1>
            <p class="text-gray-400">Masuk ke akun Anda</p>
        </div>

        {{-- Jika masih login, beri opsi logout --}}
        @auth
            <div class="mb-4 rounded-xl border border-yellow-500/20 bg-yellow-500/5 p-4 text-sm text-yellow-300">
                Anda sudah login sebagai <span class="font-semibold">{{ auth()->user()->name }}</span>.
                <div class="mt-3 flex items-center gap-2">
                    <a href="{{ route('home') }}" class="rounded-lg bg-yellow-400 px-3 py-2 text-xs font-semibold text-gray-900 hover:bg-yellow-500">
                        Buka Beranda
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg border border-gray-700 px-3 py-2 text-xs text-gray-300 hover:bg-gray-800">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        @endauth

        <!-- Login Form -->
        <div class="glass rounded-xl p-8">
            <form method="POST" action="{{ route('login') }}" x-data="{ showPassword: false }"
                  x-effect="window.lucide && window.lucide.createIcons()">
                @csrf

                <!-- Email -->
                <div class="mb-6">
                    <label for="email" class="mb-2 block text-sm font-medium">Email</label>
                    <div class="relative">
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                               placeholder="example@gmail.com"
                               class="w-full rounded-lg border border-gray-700 bg-gray-800 px-4 py-3 pl-10 transition focus:border-yellow-400 focus:outline-none"
                               required autofocus>
                        <i data-lucide="mail" class="absolute left-3 top-3.5 h-5 w-5 text-gray-400"></i>
                    </div>
                    @error('email')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-6">
                    <label for="password" class="mb-2 block text-sm font-medium">Password</label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" id="password" name="password"
                               placeholder="Masukkan password"
                               class="w-full rounded-lg border border-gray-700 bg-gray-800 px-4 py-3 pl-10 pr-10 transition focus:border-yellow-400 focus:outline-none"
                               required>
                        <i data-lucide="lock" class="absolute left-3 top-3.5 h-5 w-5 text-gray-400"></i>

                        <button type="button" @click="showPassword = !showPassword"
                                class="absolute right-3 top-3.5 text-gray-400 hover:text-gray-300"
                                :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'">
                            <i :data-lucide="showPassword ? 'eye-off' : 'eye'" class="h-5 w-5"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="mb-6 flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember"
                               class="rounded border-gray-700 bg-gray-800 text-yellow-400 focus:ring-yellow-400">
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
                        class="neon-glow w-full rounded-lg bg-yellow-400 py-3 font-semibold text-gray-900 transition hover:bg-yellow-500">
                    <i data-lucide="log-in" class="mr-2 inline h-5 w-5"></i>
                    Masuk
                </button>

                <!-- Divider -->
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-700"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="bg-gray-900 px-4 text-gray-400">Atau</span>
                    </div>
                </div>

                <!-- Social Login (Optional) -->
                <div class="grid grid-cols-2 gap-3">
                    <button type="button"
                            class="flex items-center justify-center rounded-lg border border-gray-700 px-4 py-2 transition hover:bg-gray-800">
                        <img src="/images/google-icon.svg" alt="Google" class="mr-2 h-5 w-5">
                        Google
                    </button>
                    <button type="button"
                            class="flex items-center justify-center rounded-lg border border-gray-700 px-4 py-2 transition hover:bg-gray-800">
                        <i data-lucide="facebook" class="mr-2 h-5 w-5"></i>
                        Facebook
                    </button>
                </div>

                <!-- Register Link -->
                <p class="mt-6 text-center text-gray-400">
                    Belum punya akun?
                    <a href="{{ route('register') }}" class="font-semibold text-yellow-400 hover:underline">
                        Daftar sekarang
                    </a>
                </p>
            </form>
        </div>
    </div>
</div>
@endsection
