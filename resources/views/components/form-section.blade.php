@props(['title' => '', 'description' => ''])

<div {{ $attributes->merge(['class' => 'glass rounded-xl p-6']) }}>
    @if($title)
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-yellow-400">{{ $title }}</h3>
            @if($description)
                <p class="mt-1 text-sm text-gray-400">{{ $description }}</p>
            @endif
        </div>
    @endif
    
    <div class="space-y-6">
        {{ $slot }}
    </div>
</div>