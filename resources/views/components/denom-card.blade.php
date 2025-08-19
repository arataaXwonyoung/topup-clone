@props(['denomination', 'selected' => false])

<label class="relative cursor-pointer">
    <input type="radio" 
           name="denomination_id" 
           value="{{ $denomination->id }}"
           class="hidden peer"
           {{ $selected ? 'checked' : '' }}>
    
    <div class="glass p-4 rounded-lg border-2 border-gray-700 
                peer-checked:border-yellow-400 peer-checked:bg-yellow-400/10 
                hover:border-gray-600 transition">
        @if($denomination->is_hot)
            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs px-2 py-1 rounded">HOT</span>
        @endif
        
        @if($denomination->is_promo)
            <span class="absolute -top-2 -left-2 bg-green-500 text-white text-xs px-2 py-1 rounded">PROMO</span>
        @endif
        
        <div class="font-semibold text-white">{{ $denomination->name }}</div>
        
        @if($denomination->bonus > 0)
            <div class="text-xs text-green-400">+{{ $denomination->bonus }} Bonus</div>
        @endif
        
        <div class="mt-2">
            @if($denomination->original_price && $denomination->original_price > $denomination->price)
                <div class="text-xs text-gray-500 line-through">{{ $denomination->formatted_original_price }}</div>
            @endif
            <div class="text-yellow-400 font-bold">{{ $denomination->formatted_price }}</div>
        </div>
    </div>
</label>