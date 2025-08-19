@props(['title' => '', 'description' => ''])

<div {{ $attributes->merge(['class' => 'glass rounded-xl overflow-hidden']) }}>
    @if($title)
        <div class="px-6 py-4 border-b border-gray-700">
            <h3 class="text-lg font-semibold text-yellow-400">{{ $title }}</h3>
            @if($description)
                <p class="mt-1 text-sm text-gray-400">{{ $description }}</p>
            @endif
        </div>
    @endif
    
    <div class="p-6">
        {{ $slot }}
    </div>
</div>