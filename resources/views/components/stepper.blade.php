@props(['current' => 1, 'steps' => []])

<div class="flex items-center justify-between mb-8">
    @foreach($steps as $index => $step)
        <div class="flex items-center {{ $index < count($steps) - 1 ? 'flex-1' : '' }}">
            <div class="relative">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold
                    {{ $current >= $loop->iteration ? 'bg-yellow-400 text-gray-900' : 'bg-gray-700 text-gray-400' }}">
                    {{ $loop->iteration }}
                </div>
                @if($current >= $loop->iteration)
                    <div class="absolute inset-0 rounded-full animate-ping bg-yellow-400 opacity-25"></div>
                @endif
            </div>
            
            <div class="ml-3">
                <p class="text-sm font-semibold {{ $current >= $loop->iteration ? 'text-yellow-400' : 'text-gray-500' }}">
                    {{ $step }}
                </p>
            </div>
            
            @if($index < count($steps) - 1)
                <div class="flex-1 h-1 mx-4 {{ $current > $loop->iteration ? 'bg-yellow-400' : 'bg-gray-700' }}"></div>
            @endif
        </div>
    @endforeach
</div>