<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\EventStatus;
use App\Models\Event;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CheckEventStatus
{
    /**
     * Memeriksa apakah sesi kegiatan masih aktif dan dapat diisi.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->route('token');

        if (! $token) {
            abort(404, 'Token kegiatan tidak ditemukan.');
        }

        $event = Event::where('qr_token', $token)->first();

        if (! $event) {
            return response()->view('presensi.invalid', [
                'message' => 'Tautan presensi tidak valid atau kegiatan tidak ditemukan.',
            ], 404);
        }

        if ($event->status !== EventStatus::APPROVED) {
            return response()->view('presensi.closed', [
                'event' => $event,
                'message' => 'Sesi daftar hadir untuk kegiatan ini sedang ditutup atau telah selesai.',
            ]);
        }

        // Simpan instance event ke dalam request attribute agar controller tidak perlu query ulang
        $request->attributes->set('current_event', $event);

        return $next($request);
    }
}
