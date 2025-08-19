@extends('layouts.app')

@section('title', 'Reset Password - Takapedia Clone')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <!-- Logo & Title -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-yellow-400 neon-text mb-2">⚡ Takapedia</h1>
            <h2 class="text-2xl font-semibold text-gray-100">Reset Password</h2>
            <p class="mt-2 text-gray-400">
                Buat password baru untuk akun Anda
            </p>
        </div>

        <!-- Reset Password Form -->
        <div class="glass rounded-xl p-8">
            <form method="POST" action="{{ route('password.store') }}">
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Email Address -->
                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-2">
                        Email
                    </label>
                    <div class="relative">
                        <input id="email" 
                               type="email" 
                               name="email" 
                               value="{{ old('email', $request->email) }}" 
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

                <!-- Password -->
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
                        Password Baru
                    </label>
                    <div class="relative" x-data="{ showPassword: false }">
                        <input id="password" 
                               :type="showPassword ? 'text' : 'password'"
                               name="password" 
                               required 
                               autocomplete="new-password"
                               class="w-full px-4 py-3 pl-10 pr-10 bg-gray-800 border border-gray-700 rounded-lg focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400/20 text-gray-100"
                               placeholder="Minimal 8 karakter">
                        <i data-lucide="lock" class="absolute left-3 top-3.5 w-5 h-5 text-gray-500"></i>
                        <button type="button" 
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-3.5 text-gray-500 hover:text-gray-300">
                            <i data-lucide="eye" x-show="!showPassword" class="w-5 h-5"></i>
                            <i data-lucide="eye-off" x-show="showPassword" class="w-5 h-5"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="mb-6">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-300 mb-2">
                        Konfirmasi Password Baru
                    </label>
                    <div class="relative" x-data="{ showPassword: false }">
                        <input id="password_confirmation" 
                               :type="showPassword ? 'text' : 'password'"
                               name="password_confirmation" 
                               required 
                               autocomplete="new-password"
                               class="w-full px-4 py-3 pl-10 pr-10 bg-gray-800 border border-gray-700 rounded-lg focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400/20 text-gray-100"
                               placeholder="Ulangi password baru">
                        <i data-lucide="lock" class="absolute left-3 top-3.5 w-5 h-5 text-gray-500"></i>
                        <button type="button" 
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-3.5 text-gray-500 hover:text-gray-300">
                            <i data-lucide="eye" x-show="!showPassword" class="w-5 h-5"></i>
                            <i data-lucide="eye-off" x-show="showPassword" class="w-5 h-5"></i>
                        </button>
                    </div>
                    @error('password_confirmation')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Requirements -->
                <div class="mb-6 p-4 rounded-lg bg-gray-800 border border-gray-700">
                    <p class="text-sm font-medium text-gray-300 mb-2">Password harus memenuhi:</p>
                    <ul class="text-sm text-gray-500 space-y-1">
                        <li class="flex items-center">
                            <i data-lucide="check" class="w-4 h-4 mr-2 text-green-400"></i>
                            Minimal 8 karakter
                        </li>
                        <li class="flex items-center">
                            <i data-lucide="check" class="w-4 h-4 mr-2 text-green-400"></i>
                            Kombinasi huruf dan angka
                        </li>
                        <li class="flex items-center">
                            <i data-lucide="check" class="w-4 h-4 mr-2 text-green-400"></i>
                            Minimal satu huruf kapital
                        </li>
                    </ul>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full py-3 px-4 bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-semibold rounded-lg transition duration-200 neon-glow">
                    <i data-lucide="key" class="inline w-5 h-5 mr-2"></i>
                    Reset Password
                </button>
            </form>
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