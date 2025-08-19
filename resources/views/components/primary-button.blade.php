@props(['type' => 'submit', 'disabled' => false])

<button 
    {{ $attributes->merge([
        'type' => $type, 
        'class' => 'px-6 py-3 bg-yellow-400 text-gray-900 rounded-lg font-semibold hover:bg-yellow-500 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2 focus:ring-offset-gray-900 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-yellow-400/25 neon-glow'
    ]) }}
    {{ $disabled ? 'disabled' : '' }}>
    {{ $slot }}
</button>