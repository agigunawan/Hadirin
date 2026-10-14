<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\EventStatus;
use App\Http\Requests\StoreAttendeeRequest;
use App\Http\Requests\StoreEvaluationRequest;
use App\Models\Attendee;
use App\Models\Event;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class PresensiController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {}

    public function show(Request $request, string $token): View
    {
        /** @var Event $event */
        $event = $request->attributes->get('current_event')
            ?? Event::with('room')->where('qr_token', $token)->firstOrFail();

        return view('presensi.form', [
            'event' => $event,
            'token' => $token,
        ]);
    }

    public function store(StoreAttendeeRequest $request, string $token): RedirectResponse
    {
        $event = Event::where('qr_token', $token)->firstOrFail();

        if ($event->status !== EventStatus::APPROVED) {
            return back()->withErrors([
                'status' => 'Sesi daftar hadir untuk kegiatan ini sedang ditutup atau telah selesai.',
            ]);
        }

        $validated = $request->validated();

        // Cek duplikasi presensi (Zero Duplicate Submissions)
        if (!empty($validated['nip_or_id'])) {
            $isAlreadySubmitted = $event->attendees()
                ->where('nip_or_id', $validated['nip_or_id'])
                ->exists();

            if ($isAlreadySubmitted) {
                return back()->withInput()->withErrors([
                    'nip_or_id' => 'Data kehadiran dengan NIP/NIK tersebut sudah tercatat pada kegiatan ini.',
                ]);
            }
        } elseif (!empty($validated['full_name']) && $validated['full_name'] !== 'Peserta Hadir') {
            $isAlreadySubmitted = $event->attendees()
                ->where('full_name', $validated['full_name'])
                ->exists();

            if ($isAlreadySubmitted) {
                return back()->withInput()->withErrors([
                    'full_name' => 'Data kehadiran atas nama peserta tersebut sudah tercatat pada kegiatan ini.',
                ]);
            }
        }

        $attendee = DB::transaction(function () use ($event, $validated, $request) {
            $attendee = $event->attendees()->create($validated);

            $idString = $attendee->nip_or_id ? " ({$attendee->nip_or_id})" : '';
            $this->auditLogService->log(
                action: 'SUBMIT_KEHADIRAN',
                detail: "Peserta {$attendee->full_name}{$idString} mengisi daftar hadir kegiatan '{$event->title}'",
                username: $attendee->nip_or_id ?? $attendee->full_name ?? 'Guest',
                ipAddress: $request->ip()
            );

            return $attendee;
        });

        // Alihkan peserta ke pengisian survei IKM
        return redirect()->route('presensi.survey', [
            'token' => $token,
            'attendee' => $attendee->id,
        ])->with('success', 'Presensi Anda berhasil dicatat! Mohon berikan penilaian kepuasan layanan berikut.');
    }

    public function survey(string $token, string $attendee): View|RedirectResponse
    {
        $event = Event::where('qr_token', $token)->firstOrFail();
        $attendeeModel = Attendee::where('event_id', $event->id)->findOrFail($attendee);

        // Jika peserta sudah mengisi evaluasi, langsung alihkan ke halaman sukses
        if ($attendeeModel->evaluation()->exists()) {
            return redirect()->route('presensi.success', [
                'token' => $token,
                'attendee' => $attendeeModel->id,
            ]);
        }

        return view('presensi.survey', [
            'event' => $event,
            'attendee' => $attendeeModel,
            'token' => $token,
        ]);
    }

    public function storeSurvey(StoreEvaluationRequest $request, string $token, string $attendee): RedirectResponse
    {
        $event = Event::where('qr_token', $token)->firstOrFail();
        $attendeeModel = Attendee::where('event_id', $event->id)->findOrFail($attendee);

        if ($attendeeModel->evaluation()->exists()) {
            return redirect()->route('presensi.success', [
                'token' => $token,
                'attendee' => $attendeeModel->id,
            ]);
        }

        DB::transaction(function () use ($request, $attendeeModel, $event) {
            $attendeeModel->evaluation()->create($request->validated());

            $this->auditLogService->log(
                action: 'SUBMIT_IKM',
                detail: "Peserta {$attendeeModel->full_name} mengisi penilaian IKM untuk kegiatan '{$event->title}'",
                username: $attendeeModel->nip_or_id,
                ipAddress: $request->ip()
            );
        });

        return redirect()->route('presensi.success', [
            'token' => $token,
            'attendee' => $attendeeModel->id,
        ]);
    }

    public function success(string $token, string $attendee): View
    {
        $event = Event::with('room')->where('qr_token', $token)->firstOrFail();
        $attendeeModel = Attendee::with('evaluation')->where('event_id', $event->id)->findOrFail($attendee);

        return view('presensi.success', [
            'event' => $event,
            'attendee' => $attendeeModel,
        ]);
    }
}
