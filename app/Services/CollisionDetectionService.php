<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EventStatus;
use App\Models\Event;
use Carbon\CarbonInterface;

final class CollisionDetectionService
{
    /**
     * Memeriksa apakah terjadi irisan jadwal pemakaian ruangan pada tanggal yang sama.
     * Logika overlap: existing.start < new.end AND existing.end > new.start
     */
    public function hasCollision(
        string $roomId,
        string $eventDate,
        int $startMinutes,
        int $endMinutes,
        ?string $ignoreEventId = null,
    ): bool {
        return Event::query()
            ->where('room_id', $roomId)
            ->whereDate('event_date', $eventDate)
            ->whereIn('status', [EventStatus::APPROVED, EventStatus::PENDING])
            ->when($ignoreEventId, fn ($query) => $query->where('id', '!=', $ignoreEventId))
            ->where(function ($query) use ($startMinutes, $endMinutes) {
                $query->where('start_minutes', '<', $endMinutes)
                    ->where('end_minutes', '>', $startMinutes);
            })
            ->exists();
    }

    /**
     * Mengambil daftar kegiatan yang bentrok untuk ditampilkan sebagai peringatan.
     */
    public function getConflictingEvents(
        string $roomId,
        string $eventDate,
        int $startMinutes,
        int $endMinutes,
        ?string $ignoreEventId = null,
    ): array {
        return Event::with('room', 'creator')
            ->where('room_id', $roomId)
            ->whereDate('event_date', $eventDate)
            ->whereIn('status', [EventStatus::APPROVED, EventStatus::PENDING])
            ->when($ignoreEventId, fn ($query) => $query->where('id', '!=', $ignoreEventId))
            ->where(function ($query) use ($startMinutes, $endMinutes) {
                $query->where('start_minutes', '<', $endMinutes)
                    ->where('end_minutes', '>', $startMinutes);
            })
            ->get()
            ->all();
    }
}
