<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::published()
            ->latest('published_at')
            ->paginate(12);
            
        $featuredArticles = Article::published()
            ->where('is_featured', true)
            ->latest('published_at')
            ->limit(3)
            ->get();
            
        return view('articles.index', compact('articles', 'featuredArticles'));
    }
    
    public function show($slug)
    {
        $article = Article::where('slug', $slug)
            ->published()
            ->firstOrFail();
            
        $article->incrementViewCount();
        
        $relatedArticles = Article::published()
            ->where('category', $article->category)
            ->where('id', '!=', $article->id)
            ->limit(4)
            ->get();
            
        return view('articles.show', compact('article', 'relatedArticles'));
    }
}