<?php

namespace App\Livewire\Central;

use App\Models\Central\News;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.central')]
class NewsHome extends Component
{
    public function render()
    {
        $latestNews = News::published()
            ->with(['author', 'media', 'tags'])
            ->latest('published_at')
            ->take(6)
            ->get();

        $featuredNews = News::published()
            ->withAnyTags(['featured'])
            ->with(['author', 'media'])
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('livewire.central.news-home', [
            'latestNews' => $latestNews,
            'featuredNews' => $featuredNews,
        ]);
    }
}
