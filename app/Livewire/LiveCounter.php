<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Event;
use Livewire\Component;

final class LiveCounter extends Component
{
    public string $eventId;

    public function mount(string $eventId): void
    {
        $this->eventId = $eventId;
    }

    public function render()
    {
        $event = Event::withCount('attendees')->findOrFail($this->eventId);

        return view('livewire.live-counter', [
            'count' => $event->attendees_count,
            'event' => $event,
        ]);
    }
}
