@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="glass rounded-xl p-6 mb-8">
        <h1 class="text-3xl font-bold text-yellow-400 mb-2">Edit Profile</h1>
        <p class="text-gray-400">Update your account information</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Profile Form -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Personal Information -->
            <div class="glass rounded-xl p-6">
                <h2 class="text-xl font-semibold text-yellow-400 mb-6">Personal Information</h2>
                
                <form method="POST" action="{{ route('user.profile.update') }}">
                    @csrf
                    @method('PATCH')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2">Name</label>
                            <input type="text" 
                                   name="name" 
                                   value="{{ old('name', auth()->user()->name) }}"
                                   class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none"
                                   required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Email</label>
                            <input type="email" 
                                   value="{{ auth()->user()->email }}"
                                   class="w-full px-4 py-2 bg-gray-700 rounded-lg border border-gray-600"
                                   disabled>
                            <p class="mt-1 text-xs text-gray-400">Email cannot be changed</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Phone</label>
                            <input type="tel" 
                                   name="phone" 
                                   value="{{ old('phone', auth()->user()->phone) }}"
                                   class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">WhatsApp</label>
                            <input type="tel" 
                                   name="whatsapp" 
                                   value="{{ old('whatsapp', auth()->user()->whatsapp) }}"
                                   class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Date of Birth</label>
                            <input type="date" 
                                   name="date_of_birth" 
                                   value="{{ old('date_of_birth', auth()->user()->date_of_birth?->format('Y-m-d')) }}"
                                   class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Gender</label>
                            <select name="gender" 
                                    class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none">
                                <option value="">Select Gender</option>
                                <option value="male" {{ auth()->user()->gender == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ auth()->user()->gender == 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ auth()->user()->gender == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium mb-2">Address</label>
                        <textarea name="address" 
                                  rows="3"
                                  class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none">{{ old('address', auth()->user()->address) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">City</label>
                            <input type="text" 
                                   name="city" 
                                   value="{{ old('city', auth()->user()->city) }}"
                                   class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Province</label>
                            <input type="text" 
                                   name="province" 
                                   value="{{ old('province', auth()->user()->province) }}"
                                   class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Postal Code</label>
                            <input type="text" 
                                   name="postal_code" 
                                   value="{{ old('postal_code', auth()->user()->postal_code) }}"
                                   class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none">
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit" 
                                class="px-6 py-3 bg-yellow-400 text-gray-900 rounded-lg font-semibold hover:bg-yellow-500 transition">
                            Update Profile
                        </button>
                    </div>
                </form>
            </div>

            <!-- Change Password -->
            <div class="glass rounded-xl p-6">
                <h2 class="text-xl font-semibold text-yellow-400 mb-6">Change Password</h2>
                
                <form method="POST" action="{{ route('user.profile.password') }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Current Password</label>
                            <input type="password" 
                                   name="current_password"
                                   class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none"
                                   required>
                            @error('current_password')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">New Password</label>
                            <input type="password" 
                                   name="password"
                                   class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none"
                                   required>
                            @error('password')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Confirm New Password</label>
                            <input type="password" 
                                   name="password_confirmation"
                                   class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none"
                                   required>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit" 
                                class="px-6 py-3 bg-gray-700 text-white rounded-lg font-semibold hover:bg-gray-600 transition">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>

            <!-- Theme Settings -->
            <div class="glass rounded-xl p-6" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }">
                <h2 class="text-xl font-semibold text-yellow-400 mb-6">Theme Settings</h2>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium">Dark Mode</p>
                            <p class="text-sm text-gray-400">Toggle dark/light theme</p>
                        </div>
                        <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode); location.reload()"
                                :class="darkMode ? 'bg-yellow-400' : 'bg-gray-600'"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors">
                            <span :class="darkMode ? 'translate-x-6' : 'translate-x-1'"
                                  class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Account Stats -->
            <div class="glass rounded-xl p-6">
                <h3 class="text-lg font-semibold text-yellow-400 mb-4">Account Stats</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Member Since</span>
                        <span>{{ auth()->user()->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Level</span>
                        <span class="text-yellow-400">{{ ucfirst(auth()->user()->level) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Points</span>
                        <span>{{ number_format(auth()->user()->points) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Balance</span>
                        <span>Rp {{ number_format(auth()->user()->balance, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Notification Settings -->
            <div class="glass rounded-xl p-6">
                <h3 class="text-lg font-semibold text-yellow-400 mb-4">Notifications</h3>
                
                <form method="POST" action="{{ route('user.profile.notifications') }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="space-y-3">
                        <label class="flex items-center justify-between">
                            <span class="text-sm">Email Notifications</span>
                            <input type="checkbox" 
                                   name="email_notifications" 
                                   value="1"
                                   {{ auth()->user()->preferences['notifications']['email'] ?? false ? 'checked' : '' }}
                                   class="rounded bg-gray-800 border-gray-700 text-yellow-400">
                        </label>
                        
                        <label class="flex items-center justify-between">
                            <span class="text-sm">WhatsApp Notifications</span>
                            <input type="checkbox" 
                                   name="whatsapp_notifications" 
                                   value="1"
                                   {{ auth()->user()->preferences['notifications']['whatsapp'] ?? false ? 'checked' : '' }}
                                   class="rounded bg-gray-800 border-gray-700 text-yellow-400">
                        </label>
                        
                        <label class="flex items-center justify-between">
                            <span class="text-sm">Promo Notifications</span>
                            <input type="checkbox" 
                                   name="promo_notifications" 
                                   value="1"
                                   {{ auth()->user()->preferences['notifications']['promo'] ?? false ? 'checked' : '' }}
                                   class="rounded bg-gray-800 border-gray-700 text-yellow-400">