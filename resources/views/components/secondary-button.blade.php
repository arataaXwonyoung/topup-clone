@props(['type' => 'button', 'disabled' => false])

<button 
    {{ $attributes->merge([
        'type' => $type,
        'class' => 'px-6 py-3 bg-gray-700 text-gray-100 rounded-lg font-semibold hover:bg-gray-600 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 focus:ring-offset-gray-900 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed'
    ]) }}
    {{ $disabled ? 'disabled' : '' }}>
    {{ $slot }}
</button>