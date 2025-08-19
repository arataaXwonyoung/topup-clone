@props(['label' => '', 'for' => '', 'required' => false, 'error' => null, 'help' => ''])

<div {{ $attributes->merge(['class' => 'space-y-2']) }}>
    @if($label)
        <x-input-label :for="$for" :value="$label" :required="$required" />
    @endif
    
    {{ $slot }}
    
    @if($help)
        <p class="text-xs text-gray-500">{{ $help }}</p>
    @endif
    
    @if($error)
        <x-input-error :messages="$error" />
    @endif
</div>