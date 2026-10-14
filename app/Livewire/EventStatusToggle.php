<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Services\AuditLogService;
use Livewire\Component;

final class EventStatusToggle extends Component
{
    public Event $event;
    public bool $isOpen;

    public function mount(Event $event): void
    {
        $this->event = $event;
        $this->isOpen = $event->status === EventStatus::APPROVED;
    }

    public function toggleStatus(): void
    {
        $newStatus = $this->isOpen ? EventStatus::CLOSED : EventStatus::APPROVED;
        $this->event->status = $newStatus;
        $this->event->save();

        $this->isOpen = $this->event->status === EventStatus::APPROVED;

        app(AuditLogService::class)->log(
            action: 'TOGGLE_STATUS',
            detail: "Status presensi kegiatan '{$this->event->title}' diubah menjadi {$newStatus->label()}"
        );

        $this->dispatch('event-status-updated', status: $newStatus->value);
    }

    public function render()
    {
        return view('livewire.event-status-toggle');
    }
}
