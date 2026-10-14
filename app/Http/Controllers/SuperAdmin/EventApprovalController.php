<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Actions\Events\ApproveEventAction;
use App\Actions\Events\RejectEventAction;
use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class EventApprovalController extends Controller
{
    public function index(Request $request): View
    {
        $statusFilter = $request->query('status');

        $events = Event::with('room', 'creator')
            ->withCount('attendees')
            ->when($statusFilter, fn ($q) => $q->where('status', $statusFilter))
            ->orderByRaw("CASE WHEN status = 'PENDING' THEN 1 ELSE 2 END")
            ->orderBy('event_date', 'asc')
            ->paginate(15);

        return view('super-admin.events.index', [
            'events' => $events,
            'currentFilter' => $statusFilter,
        ]);
    }

    public function approve(Event $event, ApproveEventAction $action, Request $request): RedirectResponse
    {
        try {
            $action->execute($event, $request->user());

            return back()->with('success', "Kegiatan '{$event->title}' berhasil disetujui dan token presensi telah aktif.");
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function reject(Event $event, RejectEventAction $action, Request $request): RedirectResponse
    {
        $request->validate([
            'rejection_note' => ['required', 'string', 'max:500'],
        ], [
            'rejection_note.required' => 'Catatan alasan penolakan wajib diisi agar pengaju dapat memperbaiki agenda.',
        ]);

        try {
            $action->execute($event, (string) $request->input('rejection_note'), $request->user());

            return back()->with('info', "Kegiatan '{$event->title}' telah ditolak dengan catatan.");
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function toggleStatus(Event $event, Request $request): JsonResponse|RedirectResponse
    {
        if (!in_array($event->status->value, ['APPROVED', 'CLOSED'], true)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status agenda ini tidak dapat diubah.',
                ], 422);
            }

            return back()->withErrors(['error' => 'Status agenda ini tidak dapat diubah.']);
        }

        $newStatus = $event->status === EventStatus::APPROVED
            ? EventStatus::CLOSED
            : EventStatus::APPROVED;

        $event->status = $newStatus;
        $event->save();

        app(AuditLogService::class)->log(
            action: 'TOGGLE_STATUS',
            detail: "Status presensi kegiatan '{$event->title}' diubah menjadi {$newStatus->label()}"
        );

        $isOpen = $newStatus === EventStatus::APPROVED;
        $message = $isOpen
            ? "Presensi kegiatan '{$event->title}' berhasil dibuka kembali."
            : "Presensi kegiatan '{$event->title}' berhasil ditutup.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'isOpen' => $isOpen,
                'status' => $newStatus->value,
                'label' => $isOpen ? 'Buka' : 'Tutup',
                'badgeLabel' => $newStatus->label(),
                'badgeClass' => $newStatus->badgeClass(),
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }
}
