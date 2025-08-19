@extends('layouts.app')

@section('title', 'Artikel & Promo')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="glass rounded-xl p-6 mb-8">
        <h1 class="text-3xl font-bold text-yellow-400 mb-4">Artikel & Promo</h1>
        <p class="text-gray-400">Tips, panduan, dan promo terbaru untuk para gamers!</p>
    </div>

    @if(isset($featuredArticles) && $featuredArticles->count() > 0)
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">Artikel Pilihan</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($featuredArticles as $article)
            <a href="{{ route('articles.show', $article->slug) }}" 
               class="glass rounded-xl overflow-hidden hover:transform hover:scale-105 transition">
                @if($article->featured_image)
                <img src="{{ asset($article->featured_image) }}" 
                     alt="{{ $article->title }}"
                     class="w-full h-48 object-cover">
                @else
                <div class="w-full h-48 bg-gradient-to-br from-purple-600 to-blue-600"></div>
                @endif
                <div class="p-4">
                    <span class="text-xs text-yellow-400">{{ ucfirst($article->category) }}</span>
                    <h3 class="font-semibold mt-1">{{ $article->title }}</h3>
                    <p class="text-sm text-gray-400 mt-2">{{ Str::limit($article->excerpt, 100) }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @if(isset($articles) && $articles->count() > 0)
            @foreach($articles as $article)
            <a href="{{ route('articles.show', $article->slug) }}" 
               class="glass rounded-xl overflow-hidden hover:transform hover:scale-105 transition">
                @if($article->featured_image)
                <img src="{{ asset($article->featured_image) }}" 
                     alt="{{ $article->title }}"
                     class="w-full h-48 object-cover">
                @else
                <div class="w-full h-48 bg-gradient-to-br from-indigo-600 to-purple-600"></div>
                @endif
                <div class="p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs text-yellow-400">{{ ucfirst($article->category) }}</span>
                        <span class="text-xs text-gray-400">{{ $article->published_at->diffForHumans() }}</span>
                    </div>
                    <h3 class="font-semibold">{{ $article->title }}</h3>
                    <p class="text-sm text-gray-400 mt-2">{{ Str::limit($article->excerpt, 80) }}</p>
                    <div class="flex items-center justify-between mt-3">
                        <span class="text-xs text-gray-500">{{ $article->view_count }} views</span>
                        <span class="text-yellow-400 text-sm">Baca →</span>
                    </div>
                </div>
            </a>
            @endforeach
        @else
            <div class="col-span-3 text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-200">Belum ada artikel</h3>
                <p class="mt-1 text-sm text-gray-400">Artikel akan segera hadir.</p>
            </div>
        @endif
    </div>

    @if(isset($articles) && $articles->count() > 0)
    <div class="mt-8">
        {{ $articles->links() }}
    </div>
    @endif
</div>
@endsection