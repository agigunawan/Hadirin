<?php

declare(strict_types=1);

namespace App\Actions\Events;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;

final class RejectEventAction
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    public function execute(Event $event, string $rejectionNote, User $actor): Event
    {
        return DB::transaction(function () use ($event, $rejectionNote, $actor) {
            $event->status = EventStatus::REJECTED;
            $event->rejection_note = trim($rejectionNote);
            $event->save();

            $this->auditLogService->log(
                action: 'REJECT_KEGIATAN',
                detail: "Super Admin {$actor->name} menolak kegiatan '{$event->title}'. Catatan: {$event->rejection_note}",
                username: $actor->username
            );

            return $event;
        });
    }
}
