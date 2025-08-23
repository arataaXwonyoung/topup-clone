@extends('layouts.app')

@section('title', 'My Reviews')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="glass rounded-xl p-6 mb-8">
        <h1 class="text-3xl font-bold text-yellow-400">My Reviews</h1>
        <p class="text-gray-400 mt-2">Kelola ulasan dan rating untuk produk yang kamu beli</p>
    </div>

    @if($reviews->count() > 0)
        <div class="space-y-4">
            @foreach($reviews as $review)
            <div class="glass rounded-xl p-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-4 mb-3">
                            <h3 class="font-semibold text-lg">{{ $review->game->name }}</h3>
                            <div class="flex text-yellow-400">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $review->rating)
                                        <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                                            <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                        </svg>
                                    @endif
                                @endfor
                            </div>
                            @if($review->is_verified)
                            <span class="px-2 py-1 bg-green-500 text-white text-xs rounded">Verified</span>
                            @endif
                        </div>
                        
                        <p class="text-gray-300 mb-3">{{ $review->comment }}</p>
                        
                        <div class="flex items-center space-x-4 text-sm text-gray-400">
                            <span>Order #{{ $review->order->invoice_no }}</span>
                            <span>•</span>
                            <span>{{ $review->created_at->format('d M Y') }}</span>
                            @if($review->helpful_count > 0)
                            <span>•</span>
                            <span>{{ $review->helpful_count }} found helpful</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="flex space-x-2">
                        <button onclick="editReview({{ $review->id }})" 
                                class="px-3 py-1 bg-gray-700 rounded hover:bg-gray-600 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>
                        <form action="{{ route('user.reviews.destroy', $review) }}" method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this review?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="px-3 py-1 bg-red-600 rounded hover:bg-red-700 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $reviews->links() }}
        </div>
    @else
        <div class="glass rounded-xl p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-200">Belum ada review</h3>
            <p class="mt-1 text-sm text-gray-400">Kamu belum memberikan review untuk produk apapun.</p>
            <div class="mt-6">
                <a href="{{ route('user.orders') }}" class="px-4 py-2 bg-yellow-400 text-gray-900 rounded-lg font-semibold hover:bg-yellow-500 transition">
                    Lihat Order Saya
                </a>
            </div>
        </div>
    @endif
</div>

<!-- Edit Review Modal -->
<div id="editReviewModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="fixed inset-0 bg-black opacity-50"></div>
    <div class="relative glass rounded-xl p-6 max-w-md w-full">
        <h3 class="text-xl font-semibold mb-4">Edit Review</h3>
        <form id="editReviewForm" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Rating</label>
                <div class="flex space-x-2">
                    @for($i = 1; $i <= 5; $i++)
                    <label class="cursor-pointer">
                        <input type="radio" name="rating" value="{{ $i }}" class="hidden peer">
                        <svg class="w-8 h-8 text-gray-400 peer-checked:text-yellow-400 hover:text-yellow-400 transition" 
                             fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                        </svg>
                    </label>
                    @endfor
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Review</label>
                <textarea name="comment" rows="4" 
                          class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none"
                          required></textarea>
            </div>
            
            <div class="flex space-x-3">
                <button type="submit" 
                        class="flex-1 py-2 bg-yellow-400 text-gray-900 rounded-lg font-semibold hover:bg-yellow-500 transition">
                    Update Review
                </button>
                <button type="button" onclick="closeEditModal()" 
                        class="flex-1 py-2 border border-gray-600 rounded-lg hover:bg-gray-700 transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function editReview(reviewId) {
    // In real implementation, fetch review data and populate form
    document.getElementById('editReviewModal').style.display = 'flex';
    document.getElementById('editReviewForm').action = `/user/reviews/${reviewId}`;
}

function closeEditModal() {
    document.getElementById('editReviewModal').style.display = 'none';
}
</script>
@endpush
@endsection