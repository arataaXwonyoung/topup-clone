@props(['disabled' => false, 'rows' => 4, 'error' => false])

<textarea 
    {{ $disabled ? 'disabled' : '' }}
    rows="{{ $rows }}"
    {!! $attributes->merge([
        'class' => 'w-full px-4 py-2 bg-gray-800 rounded-lg border ' . 
                   ($error 
                    ? 'border-red-500 focus:border-red-400' 
                    : 'border-gray-700 focus:border-yellow-400') . 
                   ' focus:outline-none focus:ring-2 focus:ring-yellow-400/20 transition-colors duration-200 text-gray-100 placeholder-gray-500 disabled:bg-gray-900 disabled:text-gray-500 disabled:cursor-not-allowed resize-none'
    ]) !!}>{{ $slot }}</textarea>