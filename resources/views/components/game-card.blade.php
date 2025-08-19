@props(['game'])

<a href="{{ route('games.show', $game->slug) }}" 
   class="glass rounded-xl overflow-hidden hover-glow group">
    <div class="relative">
        <img src="{{ $game->cover_path }}" 
             alt="{{ $game->name }}" 
             class="w-full h-48 object-cover group-hover:scale-105 transition duration-300">
        
        @if($game->is_hot)
            <span class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded text-xs font-bold">HOT</span>
        @endif
        
        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition"></div>
    </div>
    
    <div class="p-4">
        <h3 class="font-semibold text-white group-hover:text-yellow-400 transition">{{ $game->name }}</h3>
        <p class="text-sm text-gray-400">{{ $game->publisher }}</p>
        
        @if($game->average_rating > 0)
            <div class="flex items-center mt-2">
                <div class="flex text-yellow-400">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= floor($game->average_rating))
                            <i data-lucide="star" class="w-3 h-3 fill-current"></i>
                        @else
                            <i data-lucide="star" class="w-3 h-3"></i>
                        @endif
                    @endfor
                </div>
                <span class="ml-1 text-xs text-gray-400">{{ number_format($game->average_rating, 1) }}</span>
            </div>
        @endif
    </div>
</a>