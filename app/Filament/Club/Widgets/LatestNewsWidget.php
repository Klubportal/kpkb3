<?php

namespace App\Filament\Club\Widgets;

use App\Models\Tenant\News;
use Filament\Widgets\Widget;
use Illuminate\Support\Str;

class LatestNewsWidget extends Widget
{
    protected string $view = 'filament.club.widgets.latest-news-widget';

    protected int | string | array $columnSpan = 'full';

    public function getNews()
    {
        return News::where('status', 'published')
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function ($news) {
                return [
                    'title' => $news->title,
                    'excerpt' => Str::limit(strip_tags($news->content), 150),
                    'published_at' => $news->published_at?->format('d.m.Y'),
                    'image' => $news->featured_image,
                    'url' => route('filament.club.resources.news.edit', $news),
                ];
            });
    }
}
