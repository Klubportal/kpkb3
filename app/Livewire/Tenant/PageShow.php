<?php

namespace App\Livewire\Tenant;

use App\Models\Tenant\Page;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.tenant.app')]
class PageShow extends Component
{
    public Page $page;

    public function mount(string $slug): void
    {
        $this->page = Page::query()
            ->where('slug', $slug)
            ->published()
            ->firstOrFail();
    }

    public function title(): string
    {
        return $this->page->title;
    }

    public function render()
    {
        return view('livewire.tenant.page-show');
    }
}
