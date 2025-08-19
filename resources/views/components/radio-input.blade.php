@props(['disabled' => false, 'checked' => false])

<input 
    type="radio"
    {{ $disabled ? 'disabled' : '' }}
    {{ $checked ? 'checked' : '' }}
    {!! $attributes->merge([
        'class' => 'border-gray-700 bg-gray-800 text-yellow-400 shadow-sm focus:ring-yellow-400 focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-900 disabled:opacity-50 disabled:cursor-not-allowed'
    ]) !!}>