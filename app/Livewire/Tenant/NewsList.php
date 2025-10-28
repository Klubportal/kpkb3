<?php

namespace App\Livewire\Tenant;

use App\Models\Tenant\News;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.tenant.app')]
#[Title('News')]
class NewsList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $selectedTag = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedTag(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $newsQuery = News::query()
            ->with(['author', 'media', 'tags'])
            ->where('status', 'published')
            ->where('published_at', '<=', now());

        if ($this->search) {
            $newsQuery->where(function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%')
                    ->orWhere('excerpt', 'like', '%' . $this->search . '%')
                    ->orWhere('content', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->selectedTag) {
            $newsQuery->withAnyTags([$this->selectedTag]);
        }

        $news = $newsQuery->orderBy('published_at', 'desc')->paginate(9);

        $allTags = News::where('status', 'published')
            ->withAnyTags()
            ->get()
            ->pluck('tags')
            ->flatten()
            ->unique('name')
            ->sortBy('name');

        return view('livewire.tenant.news-list', [
            'news' => $news,
            'allTags' => $allTags,
        ]);
    }
}
