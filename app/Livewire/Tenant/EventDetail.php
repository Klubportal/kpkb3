<?php

namespace App\Livewire\Tenant;

use App\Models\Tenant\Event;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.tenant.app')]
class EventDetail extends Component
{
    public Event $event;

    public function mount(int $id): void
    {
        $this->event = Event::query()
            ->with(['team', 'participants'])
            ->findOrFail($id);
    }

    #[Title]
    public function title(): string
    {
        return $this->event->title;
    }

    public function render()
    {
        $upcomingEvents = Event::query()
            ->where('id', '!=', $this->event->id)
            ->where('status', 'scheduled')
            ->where('start_date', '>=', now())
            ->orderBy('start_date', 'asc')
            ->limit(3)
            ->get();

        return view('livewire.tenant.event-detail', [
            'upcomingEvents' => $upcomingEvents,
        ]);
    }
}
