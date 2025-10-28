<?php

namespace App\Livewire\Tenant;

use App\Models\Tenant\Event;
use App\Models\Tenant\News;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.tenant.app')]
#[Title('Home')]
class Homepage extends Component
{
    public function render()
    {
        return view('livewire.tenant.homepage', [
            'latestNews' => News::query()
                ->where('status', 'published')
                ->where('published_at', '<=', now())
                ->orderBy('published_at', 'desc')
                ->take(3)
                ->get(),

            'upcomingEvents' => Event::query()
                ->where('status', 'scheduled')
                ->where('start_date', '>=', now())
                ->orderBy('start_date')
                ->take(4)
                ->get(),

            'featuredNews' => News::query()
                ->where('status', 'published')
                ->where('is_featured', true)
                ->where('published_at', '<=', now())
                ->orderBy('published_at', 'desc')
                ->first(),
        ]);
    }
}
