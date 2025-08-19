@extends('layouts.app')

@section('title', $article->title)
@section('description', $article->excerpt)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-sm text-gray-400 mb-6">
        <a href="{{ route('home') }}" class="hover:text-yellow-400">Home</a>
        <span>/</span>
        <a href="{{ route('articles.index') }}" class="hover:text-yellow-400">Artikel</a>
        <span>/</span>
        <span class="text-gray-300">{{ $article->title }}</span>
    </nav>
    
    <!-- Article Header -->
    <header class="mb-8">
        <div class="flex items-center gap-3 mb-4">
            <span class="px-3 py-1 bg-yellow-400/20 text-yellow-400 text-sm rounded-full">
                {{ ucfirst($article->category) }}
            </span>
            <span class="text-gray-400 text-sm">{{ $article->published_at->format('d F Y') }}</span>
        </div>
        
        <h1 class="text-3xl md:text-4xl font-bold mb-4">{{ $article->title }}</h1>
        
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <i data-lucide="user" class="w-4 h-4 text-gray-400"></i>
                    <span class="text-gray-400">{{ $article->author ?? 'Admin' }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <i data-lucide="eye" class="w-4 h-4 text-gray-400"></i>
                    <span class="text-gray-400">{{ number_format($article->view_count) }} views</span>
                </div>
            </div>
            
            <!-- Share Buttons -->
            <div class="flex items-center gap-2">
                <button onclick="shareArticle('twitter')" 
                        class="p-2 glass rounded-lg hover:bg-gray-700 transition">
                    <i data-lucide="twitter" class="w-4 h-4"></i>
                </button>
                <button onclick="shareArticle('facebook')" 
                        class="p-2 glass rounded-lg hover:bg-gray-700 transition">
                    <i data-lucide="facebook" class="w-4 h-4"></i>
                </button>
                <button onclick="copyLink()" 
                        class="p-2 glass rounded-lg hover:bg-gray-700 transition">
                    <i data-lucide="link" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
    </header>
    
    <!-- Featured Image -->
    @if($article->featured_image)
    <div class="mb-8 rounded-xl overflow-hidden">
        <img src="{{ $article->featured_image }}" 
             alt="{{ $article->title }}" 
             class="w-full h-auto">
    </div>
    @endif
    
    <!-- Article Content -->
    <div class="glass rounded-xl p-6 mb-8">
        <div class="prose prose-invert max-w-none">
            {!! $article->content !!}
        </div>
    </div>
    
    <!-- Tags -->
    @if($article->tags)
    <div class="flex flex-wrap gap-2 mb-8">
        @foreach($article->tags as $tag)
        <span class="px-3 py-1 glass rounded-full text-sm text-gray-300">
            #{{ $tag }}
        </span>
        @endforeach
    </div>
    @endif
    
    <!-- Related Articles -->
    @php
        $relatedArticles = \App\Models\Article::published()
            ->where('id', '!=', $article->id)
            ->where('category', $article->category)
            ->limit(3)
            ->get();
    @endphp
    
    @if($relatedArticles->count() > 0)
    <div class="mt-12">
        <h3 class="text-xl font-semibold mb-6">Artikel Terkait</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($relatedArticles as $related)
            <article class="glass rounded-xl overflow-hidden hover-glow">
                <div class="h-40 overflow-hidden">
                    <img src="{{ $related->featured_image ?? '/images/placeholder-article.jpg' }}" 
                         alt="{{ $related->title }}" 
                         class="w-full h-full object-cover">
                </div>
                <div class="p-4">
                    <h4 class="font-semibold mb-2 line-clamp-2">
                        <a href="{{ route('articles.show', $related->slug) }}" 
                           class="hover:text-yellow-400 transition">
                            {{ $related->title }}
                        </a>
                    </h4>
                    <p class="text-sm text-gray-400">{{ $related->published_at->format('d M Y') }}</p>
                </div>
            </article>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    // Increment view count
    fetch('{{ route("articles.increment-view", $article->slug) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    });
    
    function shareArticle(platform) {
        const url = window.location.href;
        const title = '{{ $article->title }}';
        
        let shareUrl = '';
        
        if (platform === 'twitter') {
            shareUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(title)}&url=${encodeURIComponent(url)}`;
        } else if (platform === 'facebook') {
            shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
        }
        
        window.open(shareUrl, '_blank', 'width=600,height=400');
    }
    
    function copyLink() {
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('Link berhasil disalin!');
        });
    }
    
    lucide.createIcons();
</script>
@endpush