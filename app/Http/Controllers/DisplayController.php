<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\AppConfigService;
use App\Services\QrTokenService;
use Illuminate\View\View;

final class DisplayController extends Controller
{
    public function __construct(
        private readonly QrTokenService $qrTokenService,
        private readonly AppConfigService $appConfigService,
    ) {}

    public function show(string $token): View
    {
        $event = Event::with('room', 'creator')
            ->where('qr_token', $token)
            ->firstOrFail();

        $qrSvg = $this->qrTokenService->generateSvg($token, 380);
        $presensiUrl = $this->qrTokenService->getPresensiUrl($token);
        $logoUrl = $this->appConfigService->getLogoUrl();

        return view('display.show', [
            'event' => $event,
            'qrSvg' => $qrSvg,
            'presensiUrl' => $presensiUrl,
            'logoUrl' => $logoUrl,
        ]);
    }
}
