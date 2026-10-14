<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\EventStatus;
use App\Enums\IkmRating;
use App\Http\Controllers\Controller;
use App\Models\Attendee;
use App\Models\Evaluation;
use App\Models\Event;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\View\View;

final class DashboardController extends Controller
{
    public function index(): View
    {
        $totalEvents = Event::count();
        $pendingEventsCount = Event::where('status', EventStatus::PENDING)->count();
        $approvedEventsCount = Event::where('status', EventStatus::APPROVED)->count();
        $totalAttendees = Attendee::count();

        // Rata-rata Kepuasan IKM
        $evaluations = Evaluation::all();
        $totalEvaluations = $evaluations->count();
        $ikmPercentage = 0;
        if ($totalEvaluations > 0) {
            $totalScore = $evaluations->sum(fn ($e) => $e->rating->numericScore());
            $ikmPercentage = round(($totalScore / ($totalEvaluations * 4)) * 100, 1);
        }

        // Agenda Terkini Hari Ini & Mendatang
        $recentEvents = Event::with('room', 'creator')
            ->withCount('attendees')
            ->orderBy('event_date', 'desc')
            ->orderBy('start_minutes', 'asc')
            ->limit(10)
            ->get();

        $today = Carbon::today();

        $rooms = Room::with([
            'events' => fn ($q) => $q->whereDate('event_date', $today)
                ->with(['creator'])
                ->withCount('attendees')
                ->orderBy('start_minutes', 'asc')
        ])
        ->withCount(['events' => fn ($q) => $q->whereDate('event_date', $today)])
        ->active()
        ->get();

        $roomsData = $rooms->map(function ($room) {
            return [
                'id' => (string) $room->id,
                'name' => $room->name,
                'capacity' => $room->capacity ?? '-',
                'events_count' => $room->events_count,
                'events' => $room->events->map(function ($event) {
                    return [
                        'id' => (string) $event->id,
                        'title' => $event->title,
                        'work_unit' => $event->work_unit ?: ($event->creator->name ?? 'Dinas Terkait'),
                        'creator_name' => $event->creator->name ?? '-',
                        'formatted_time' => $event->formatted_time,
                        'start_time' => Event::minutesToTime($event->start_minutes),
                        'end_time' => Event::minutesToTime($event->end_minutes),
                        'status' => $event->status->value,
                        'status_label' => $event->status->label(),
                        'status_badge_class' => $event->status->badgeClass(),
                        'attendees_count' => $event->attendees_count,
                        'is_approved' => $event->status === EventStatus::APPROVED,
                        'is_pending' => $event->status === EventStatus::PENDING,
                        'monitor_url' => route('admin-tim-kerja.events.monitor', $event->id),
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        return view('super-admin.dashboard', [
            'totalEvents' => $totalEvents,
            'pendingEventsCount' => $pendingEventsCount,
            'approvedEventsCount' => $approvedEventsCount,
            'totalAttendees' => $totalAttendees,
            'totalEvaluations' => $totalEvaluations,
            'ikmPercentage' => $ikmPercentage,
            'recentEvents' => $recentEvents,
            'rooms' => $rooms,
            'roomsData' => $roomsData,
        ]);
    }
}
