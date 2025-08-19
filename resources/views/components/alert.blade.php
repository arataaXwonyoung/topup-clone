@props(['type' => 'info', 'dismissible' => true])

@php
$classes = [
    'info' => 'bg-blue-900/50 border-blue-700 text-blue-200',
    'success' => 'bg-green-900/50 border-green-700 text-green-200',
    'warning' => 'bg-yellow-900/50 border-yellow-700 text-yellow-200',
    'danger' => 'bg-red-900/50 border-red-700 text-red-200',
][$type];

$icons = [
    'info' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
    'success' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />',
    'warning' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />',
    'danger' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
][$type];
@endphp

<div x-data="{ show: true }" 
     x-show="show" 
     {{ $attributes->merge(['class' => 'rounded-lg border p-4 ' . $classes]) }}>
    <div class="flex">
        <div class="flex-shrink-0">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                {!! $icons !!}
            </svg>
        </div>
        <div class="ml-3 flex-1">
            {{ $slot }}
        </div>
        @if($dismissible)
            <div class="ml-auto pl-3">
                <button @click="show = false" class="inline-flex text-gray-400 hover:text-gray-300">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif
    </div>
</div>