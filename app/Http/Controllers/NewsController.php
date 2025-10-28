<?php

namespace App\Http\Controllers;

use App\Models\Tenant\News;
use App\Models\Tenant\TemplateSetting;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $settings = TemplateSetting::current();
        $tag = $request->get('tag');

        $newsQuery = News::with('tags')
            ->where('status', 'published')
            ->where(function($query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });

        if ($tag) {
            $newsQuery->withAnyTags([$tag], 'news');
        }

        $news = $newsQuery->orderBy('published_at', 'desc')
            ->paginate(12);

        // Get all unique tags from published news
        $allPublishedNews = News::where('status', 'published')
            ->with('tags')
            ->get();

        $tags = $allPublishedNews
            ->pluck('tags')
            ->flatten()
            ->unique('name')
            ->pluck('name')
            ->filter();

        // Get template slug from tenant
        $tenant = tenant();
        $template = 'kp'; // Default fallback

        if ($tenant && $tenant->template_id) {
            $templateModel = \App\Models\Central\Template::find($tenant->template_id);
            if ($templateModel) {
                $template = $templateModel->slug;
            }
        }

        return view("templates.{$template}.news.index", compact(
            'settings',
            'news',
            'tags',
            'tag'
        ));
    }

    public function show($slug)
    {
        $settings = TemplateSetting::current();

        $news = News::with('tags')
            ->where('status', 'published')
            ->where('slug', $slug)
            ->firstOrFail();

        // Increment views
        $news->increment('views_count');

        // Get related news from same tags
        $tagNames = $news->tags->pluck('name')->toArray();
        if (count($tagNames) > 0) {
            $relatedNews = News::where('status', 'published')
                ->where('id', '!=', $news->id)
                ->withAnyTags($tagNames, 'news')
                ->orderBy('published_at', 'desc')
                ->limit(3)
                ->get();
        } else {
            $relatedNews = collect();
        }

        // Get template slug from tenant
        $tenant = tenant();
        $template = 'kp'; // Default fallback

        if ($tenant && $tenant->template_id) {
            $templateModel = \App\Models\Central\Template::find($tenant->template_id);
            if ($templateModel) {
                $template = $templateModel->slug;
            }
        }

        return view("templates.{$template}.news.show", compact(
            'settings',
            'news',
            'relatedNews'
        ));
    }
}
