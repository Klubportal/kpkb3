<?php

namespace App\Livewire\Tenant;

use App\Models\Tenant\News;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.tenant.app')]
class NewsDetail extends Component
{
    public News $news;

    public function mount(string $slug): void
    {
        $this->news = News::query()
            ->with(['author', 'media', 'tags', 'team'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->firstOrFail();
    }

    #[Title]
    public function title(): string
    {
        return $this->news->title;
    }

    public function render()
    {
        $relatedNews = News::query()
            ->where('id', '!=', $this->news->id)
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->when($this->news->tags->count() > 0, function ($query) {
                $query->withAnyTags($this->news->tags->pluck('name')->toArray());
            })
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();

        return view('livewire.tenant.news-detail', [
            'relatedNews' => $relatedNews,
        ]);
    }
}
