{{-- resources/views/profile/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="p-4 space-y-8">

    {{-- LOG DETAIL (opsional, tampil kalau $log ada) --}}
    @isset($log)
    <div class="glass rounded-xl p-6">
        <h2 class="text-xl font-semibold text-yellow-400 mb-6">Log Detail</h2>

        <div class="space-y-4">
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Provider</h3>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $log->provider }}</p>
            </div>

            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Event Type</h3>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $log->event_type ?? 'N/A' }}</p>
            </div>

            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Reference</h3>
                <p class="mt-1 text-sm text-gray-900 dark:text-white font-mono">{{ $log->reference ?? 'N/A' }}</p>
            </div>

            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</h3>
                @php
                    $status = $log->status;
                    $badge = [
                        'PROCESSED' => 'bg-green-100 text-green-800',
                        'PENDING'   => 'bg-yellow-100 text-yellow-800',
                        'FAILED'    => 'bg-red-100 text-red-800',
                        'IGNORED'   => 'bg-gray-100 text-gray-800',
                    ][$status] ?? 'bg-gray-100 text-gray-800';
                @endphp
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge }}">
                    {{ $status }}
                </span>
            </div>

            @if(!empty($log->error_message))
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Error Message</h3>
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $log->error_message }}</p>
            </div>
            @endif

            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Created At</h3>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">
                    {{ optional($log->created_at)?->format('d M Y H:i:s') ?? 'N/A' }}
                </p>
            </div>

            @if(!empty($log->processed_at))
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Processed At</h3>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">
                    {{ optional($log->processed_at)?->format('d M Y H:i:s') }}
                </p>
            </div>
            @endif

            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Raw Payload</h3>
                <pre class="bg-gray-100 dark:bg-gray-800 p-3 rounded-lg text-xs overflow-auto max-h-96">
{{ json_encode($log->raw_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}
                </pre>
            </div>
        </div>
    </div>
    @endisset

    {{-- EDIT PROFILE --}}
    <div class="glass rounded-xl p-6">
        <h2 class="text-xl font-semibold text-yellow-400 mb-6">Edit Profile</h2>

        <form method="POST" action="{{ route('user.profile.update') }}">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                           class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none"
                           required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                    <input type="email" value="{{ $user->email }}"
                           class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 opacity-50"
                           disabled>
                    <p class="mt-1 text-xs text-gray-500">Email cannot be changed</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Phone</label>
                    <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}"
                           class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">WhatsApp</label>
                    <input type="tel" name="whatsapp" value="{{ old('whatsapp', $user->whatsapp) }}"
                           class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none">
                    @error('whatsapp')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Date of Birth</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth', optional($user->date_of_birth)?->format('Y-m-d')) }}"
                           class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none">
                    @error('date_of_birth')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Gender</label>
                    <select name="gender"
                            class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none">
                        <option value="">Select Gender</option>
                        <option value="male"   {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other"  {{ old('gender', $user->gender) === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('gender')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Address</label>
                <textarea name="address" rows="3"
                          class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none">{{ old('address', $user->address) }}</textarea>
                @error('address')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">City</label>
                    <input type="text" name="city" value="{{ old('city', $user->city) }}"
                           class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none">
                    @error('city')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Province</label>
                    <input type="text" name="province" value="{{ old('province', $user->province) }}"
                           class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none">
                    @error('province')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Postal Code</label>
                    <input type="text" name="postal_code" value="{{ old('postal_code', $user->postal_code) }}"
                           class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none">
                    @error('postal_code')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6">
                <button type="submit"
                        class="px-6 py-3 bg-yellow-400 text-gray-900 rounded-lg font-semibold hover:bg-yellow-500 transition">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    {{-- CHANGE PASSWORD --}}
    <div class="glass rounded-xl p-6">
        <h2 class="text-xl font-semibold text-yellow-400 mb-6">Change Password</h2>

        <form method="POST" action="{{ route('user.profile.password') }}">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Current Password</label>
                    <input type="password" name="current_password"
                           class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none"
                           required>
                    @error('current_password', 'updatePassword')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">New Password</label>
                    <input type="password" name="password"
                           class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none"
                           required>
                    @error('password', 'updatePassword')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <lab
