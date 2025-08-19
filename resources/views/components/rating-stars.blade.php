@props(['rating', 'size' => 'sm', 'showNumber' => true])

@php
    $sizeClasses = [
        'xs' => 'w-3 h-3',
        'sm' => 'w-4 h-4',
        'md' => 'w-5 h-5',
        'lg' => 'w-6 h-6',
    ];
    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['sm'];
@endphp

<div class="flex items-center">
    <div class="flex text-yellow-400">
        @for($i = 1; $i <= 5; $i++)
            @if($i <= floor($rating))
                <i data-lucide="star" class="{{ $sizeClass }} fill-current"></i>
            @elseif($i - 0.5 <= $rating)
                <div class="relative">
                    <i data-lucide="star" class="{{ $sizeClass }}"></i>
                    <i data-lucide="star" class="{{ $sizeClass }} fill-current absolute inset-0" style="clip-path: inset(0 50% 0 0);"></i>
                </div>
            @else
                <i data-lucide="star" class="{{ $sizeClass }}"></i>
            @endif
        @endfor
    </div>
    
    @if($showNumber)
        <span class="ml-2 text-sm text-gray-400">{{ number_format($rating, 1) }}</span>
    @endif
</div>