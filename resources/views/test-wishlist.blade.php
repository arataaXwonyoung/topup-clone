@extends('layouts.app')

@section('title', 'Test Wishlist')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-white mb-8">Test Wishlist Functionality</h1>
    
    @auth
    <div class="bg-gray-800 p-6 rounded-lg mb-6">
        <h2 class="text-xl font-semibold mb-4">Logged in as: {{ auth()->user()->name }}</h2>
        <p>User ID: {{ auth()->id() }}</p>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach(\App\Models\Game::take(6)->get() as $game)
        <div class="bg-gray-800 p-4 rounded-lg">
            <h3 class="text-lg font-semibold text-white mb-2">{{ $game->name }}</h3>
            <p class="text-gray-400 mb-4">Game ID: {{ $game->id }}</p>
            
            <!-- Manual Wishlist Button -->
            <button onclick="toggleWishlist({{ $game->id }})" 
                    id="test-wishlist-{{ $game->id }}"
                    class="bg-yellow-400 text-gray-900 px-4 py-2 rounded hover:bg-yellow-500 transition">
                <i data-lucide="heart" class="w-4 h-4 inline mr-1"></i>
                Toggle Wishlist
            </button>
            
            <!-- Test with direct API call -->
            <button onclick="testDirectCall({{ $game->id }})" 
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition ml-2">
                Direct API Test
            </button>
            
            <div id="result-{{ $game->id }}" class="mt-2 text-sm text-gray-400"></div>
        </div>
        @endforeach
    </div>
    
    <div class="bg-gray-800 p-6 rounded-lg mt-8">
        <h3 class="text-lg font-semibold mb-4">Current Wishlist Items:</h3>
        <div id="wishlist-items">
            Loading...
        </div>
        <button onclick="loadWishlist()" class="bg-green-600 text-white px-4 py-2 rounded mt-4">
            Refresh Wishlist
        </button>
    </div>
    
    @else
    <div class="bg-red-600 text-white p-4 rounded-lg">
        <p>Please login to test wishlist functionality.</p>
        <a href="/login" class="underline">Login here</a>
    </div>
    @endauth
</div>

@push('scripts')
<script>
// Test wishlist function
async function toggleWishlist(gameId) {
    console.log('Testing wishlist for game:', gameId);
    
    try {
        const response = await fetch('/user/wishlist/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ game_id: gameId })
        });
        
        const data = await response.json();
        console.log('Response:', data);
        
        document.getElementById('result-' + gameId).innerHTML = 
            `<span class="${data.success ? 'text-green-400' : 'text-red-400'}">${data.message}</span>`;
            
        if (data.success) {
            loadWishlist();
        }
        
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('result-' + gameId).innerHTML = 
            '<span class="text-red-400">Error: ' + error.message + '</span>';
    }
}

// Direct API test
async function testDirectCall(gameId) {
    console.log('Direct API test for game:', gameId);
    
    try {
        const response = await fetch('/user/wishlist', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ game_id: gameId })
        });
        
        const text = await response.text();
        console.log('Raw response:', text);
        
        try {
            const data = JSON.parse(text);
            document.getElementById('result-' + gameId).innerHTML = 
                `<span class="${data.success ? 'text-green-400' : 'text-red-400'}">Direct: ${data.message}</span>`;
        } catch (e) {
            document.getElementById('result-' + gameId).innerHTML = 
                '<span class="text-red-400">Direct: Response not JSON - ' + text.substring(0, 100) + '</span>';
        }
        
    } catch (error) {
        console.error('Direct API Error:', error);
        document.getElementById('result-' + gameId).innerHTML = 
            '<span class="text-red-400">Direct Error: ' + error.message + '</span>';
    }
}

// Load current wishlist
async function loadWishlist() {
    try {
        const response = await fetch('/user/wishlist');
        const text = await response.text();
        
        // This might return HTML, not JSON
        document.getElementById('wishlist-items').innerHTML = text.substring(0, 200) + '...';
        
    } catch (error) {
        document.getElementById('wishlist-items').innerHTML = 'Error loading wishlist: ' + error.message;
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    loadWishlist();
});
</script>
@endpush
@endsection