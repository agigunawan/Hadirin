<?php

declare(strict_types=1);

namespace App\Http\Controllers\AdminTimKerja;

use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Models\Event;
use App\Models\Room;
use App\Services\AuditLogService;
use App\Services\QrTokenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class EventController extends Controller
{
    public function __construct(
        private readonly QrTokenService $qrTokenService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $query = Event::with('room', 'creator')
            ->withCount('attendees');

        // Jika bukan Super Admin, batasi hanya agenda yang dibuat oleh user tim kerja tersebut
        if (! $user->isSuperAdmin()) {
            $query->where('created_by', $user->id);
        } elseif ($request->filled('work_unit')) {
            // Super Admin dapat melihat semua dan memfilter berdasarkan tim kerja
            $query->where('work_unit', $request->query('work_unit'));
        }

        $events = $query->orderBy('event_date', 'desc')
            ->orderBy('start_minutes', 'asc')
            ->paginate(15)
            ->withQueryString();

        $workUnits = $user->isSuperAdmin()
            ? Event::distinct()->pluck('work_unit')->filter()->values()
            : collect();

        return view('admin-tim-kerja.events.index', [
            'events' => $events,
            'workUnits' => $workUnits,
            'selectedWorkUnit' => $request->query('work_unit'),
        ]);
    }

    public function create(): View
    {
        $rooms = Room::active()->orderBy('name')->get();

        return view('admin-tim-kerja.events.create', [
            'rooms' => $rooms,
            'resolvedConfigFields' => Event::DEFAULT_FIELDS_CONFIG,
        ]);
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $event = Event::create([
            ...$validated,
            'status' => EventStatus::PENDING,
            'qr_token' => $this->qrTokenService->generateToken(),
            'created_by' => $user->id,
        ]);

        $this->auditLogService->log(
            action: 'SUBMIT_PENGALOKASIAN_KEGIATAN',
            detail: "Admin Tim Kerja {$user->name} mengajukan agenda baru '{$event->title}' untuk tanggal {$event->event_date->format('d/m/Y')}"
        );

        return redirect()->route('admin-tim-kerja.events.index')
            ->with('success', 'Permohonan kegiatan berhasil diajukan dan sedang menunggu verifikasi Super Admin.');
    }

    public function edit(Event $event, Request $request): View
    {
        if ($event->created_by !== $request->user()->id && ! $request->user()->isSuperAdmin()) {
            abort(403, 'Anda tidak berhak mengubah agenda ini.');
        }

        $rooms = Room::active()->orderBy('name')->get();

        return view('admin-tim-kerja.events.edit', [
            'event' => $event,
            'rooms' => $rooms,
            'resolvedConfigFields' => $event->getResolvedConfigFields(),
        ]);
    }

    public function update(\App\Http\Requests\UpdateEventRequest $request, Event $event): RedirectResponse
    {
        $user = $request->user();

        if ($event->created_by !== $user->id && ! $user->isSuperAdmin()) {
            abort(403, 'Anda tidak berhak mengubah agenda ini.');
        }

        $validated = $request->validated();
        $event->update($validated);

        $this->auditLogService->log(
            action: 'UPDATE_KEGIATAN',
            detail: "Admin Tim Kerja {$user->name} memperbarui pengaturan agenda '{$event->title}'"
        );

        return redirect()->route('admin-tim-kerja.events.index')
            ->with('success', "Pengaturan agenda dan kolom presensi '{$event->title}' berhasil diperbarui.");
    }

    public function destroy(Event $event, Request $request): RedirectResponse
    {
        if ($event->created_by !== $request->user()->id && ! $request->user()->isSuperAdmin()) {
            abort(403, 'Anda tidak berhak menghapus agenda ini.');
        }

        $title = $event->title;
        $event->delete();

        $this->auditLogService->log(
            action: 'DELETE_KEGIATAN',
            detail: "Menghapus kegiatan '{$title}'"
        );

        return back()->with('info', "Kegiatan '{$title}' berhasil dihapus.");
    }

    public function updatePimpinan(Event $event, Request $request): \Illuminate\Http\JsonResponse|RedirectResponse
    {
        $user = $request->user();

        if ($event->created_by !== $user->id && ! $user->isSuperAdmin()) {
            abort(403, 'Anda tidak berhak mengatur pengesahan pimpinan untuk kegiatan ini.');
        }

        $validated = $request->validate([
            'pimpinan_name' => ['required', 'string', 'max:150'],
            'pimpinan_nip' => ['required', 'string', 'max:50'],
            'pimpinan_position' => ['required', 'string', 'max:150'],
            'pimpinan_date_text' => ['nullable', 'string', 'max:150'],
            'pimpinan_signature' => ['required', 'string'],
        ], [
            'pimpinan_name.required' => 'Nama pimpinan (beserta gelar) wajib diisi.',
            'pimpinan_nip.required' => 'NIP / NIK pimpinan wajib diisi.',
            'pimpinan_position.required' => 'Jabatan penandatangan wajib diisi.',
            'pimpinan_signature.required' => 'Tanda tangan pimpinan wajib digoreskan.',
        ]);

        $event->update([
            'pimpinan_name' => $validated['pimpinan_name'],
            'pimpinan_nip' => $validated['pimpinan_nip'],
            'pimpinan_position' => $validated['pimpinan_position'],
            'pimpinan_date_text' => $validated['pimpinan_date_text'] ?? null,
            'pimpinan_signature' => $validated['pimpinan_signature'],
        ]);

        $this->auditLogService->log(
            action: 'SET_TTD_PIMPINAN',
            detail: "User {$user->name} memperbarui profil dan tanda tangan pengesahan pimpinan kegiatan '{$event->title}'"
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Bukti pengesahan pimpinan untuk kegiatan '{$event->title}' berhasil disimpan.",
                'pimpinan' => [
                    'name' => $event->pimpinan_name,
                    'nip' => $event->pimpinan_nip,
                    'position' => $event->pimpinan_position,
                    'date_text' => $event->pimpinan_date_text,
                ],
            ]);
        }

        return back()->with('success', "Bukti pengesahan pimpinan untuk kegiatan '{$event->title}' berhasil disimpan.");
    }
}
