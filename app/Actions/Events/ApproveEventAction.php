<?php

declare(strict_types=1);

namespace App\Actions\Events;

use App\Enums\EventStatus;
use App\Exceptions\RoomCollisionException;
use App\Models\Event;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\CollisionDetectionService;
use App\Services\QrTokenService;
use Illuminate\Support\Facades\DB;

final class ApproveEventAction
{
    public function __construct(
        private readonly CollisionDetectionService $collisionService,
        private readonly QrTokenService $qrTokenService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function execute(Event $event, User $actor): Event
    {
        // 1. Pastikan tidak ada bentrok jadwal dengan agenda yang sudah disetujui sebelumnya
        if ($this->collisionService->hasCollision(
            roomId: $event->room_id,
            eventDate: $event->event_date->toDateString(),
            startMinutes: $event->start_minutes,
            endMinutes: $event->end_minutes,
            ignoreEventId: $event->id
        )) {
            throw new \RuntimeException("Tidak dapat menyetujui agenda. Terdeteksi bentrok jadwal pada ruangan ini.");
        }

        return DB::transaction(function () use ($event, $actor) {
            $event->status = EventStatus::APPROVED;
            $event->rejection_note = null;
            if (empty($event->qr_token)) {
                $event->qr_token = $this->qrTokenService->generateToken();
            }
            $event->save();

            $this->auditLogService->log(
                action: 'APPROVE_KEGIATAN',
                detail: "Super Admin {$actor->name} menyetujui kegiatan '{$event->title}' (ID: {$event->id})",
                username: $actor->username
            );

            return $event;
        });
    }
}
