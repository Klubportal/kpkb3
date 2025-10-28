<?php

namespace App\Livewire\Tenant;

use App\Models\Tenant\Event;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.tenant.app')]
#[Title('Termine')]
class EventsList extends Component
{
    use WithPagination;

    public string $filterType = 'all';
    public string $filterVisibility = 'all';

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function updatingFilterVisibility(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $eventsQuery = Event::query()
            ->with(['team'])
            ->where('status', 'scheduled')
            ->where('start_date', '>=', now());

        if ($this->filterType !== 'all') {
            $eventsQuery->where('type', $this->filterType);
        }

        if ($this->filterVisibility !== 'all') {
            $eventsQuery->where('visibility', $this->filterVisibility);
        }

        $events = $eventsQuery->orderBy('start_date', 'asc')->paginate(12);

        return view('livewire.tenant.events-list', [
            'events' => $events,
        ]);
    }
}
