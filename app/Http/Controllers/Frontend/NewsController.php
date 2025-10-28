<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Central\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index()
    {
        $news = News::published()
            ->with(['author', 'tags', 'media'])
            ->withCount('media')
            ->latest('published_at')
            ->paginate(12);

        return view('frontend.news.index', compact('news'));
    }

    public function show(string $slug)
    {
        $newsItem = News::where('slug', $slug)
            ->published()
            ->with(['author', 'tags', 'media'])
            ->firstOrFail();

        // Increment views
        $newsItem->increment('views');

        // Get related news by tags
        $related = News::published()
            ->where('id', '!=', $newsItem->id)
            ->withAnyTags($newsItem->tags)
            ->take(3)
            ->get();

        return view('frontend.news.show', compact('newsItem', 'related'));
    }
}
