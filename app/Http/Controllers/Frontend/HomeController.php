<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Central\News;
use App\Models\Central\Page;

class HomeController extends Controller
{
    public function index()
    {
        $latestNews = News::published()
            ->with(['author', 'media'])
            ->latest('published_at')
            ->take(6)
            ->get();

        $featuredNews = News::published()
            ->withAnyTags(['featured'])
            ->with(['author', 'media'])
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('frontend.home', compact('latestNews', 'featuredNews'));
    }
}
