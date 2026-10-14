<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
use App\Services\AppConfigService;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

final class AppConfigController extends Controller
{
    public function __construct(
        private readonly AppConfigService $appConfigService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function index(): View
    {
        $pimpinan = $this->appConfigService->getPimpinanProfile();
        $logoUrl = $this->appConfigService->getLogoUrl();

        return view('super-admin.config.index', [
            'pimpinan' => $pimpinan,
            'logoUrl' => $logoUrl,
        ]);
    }

    public function updatePimpinan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'nip' => ['required', 'string', 'max:30'],
            'position' => ['required', 'string', 'max:150'],
            'signature_file' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ]);

        $signatureUrl = $this->appConfigService->getPimpinanProfile()['signature_url'] ?? null;

        if ($request->hasFile('signature_file')) {
            $path = $request->file('signature_file')->store('signatures/pimpinan', 'public');
            $signatureUrl = Storage::disk('public')->url($path);
        }

        $this->appConfigService->updatePimpinanProfile([
            'name' => $validated['name'],
            'nip' => $validated['nip'],
            'position' => $validated['position'],
            'signature_url' => $signatureUrl,
        ]);

        $this->auditLogService->log(
            action: 'UPDATE_INFO_PIMPINAN',
            detail: "Super Admin {$request->user()->name} memperbarui informasi data pimpinan dinas."
        );

        return back()->with('success', 'Data pimpinan resmi dan tanda tangan berhasil diperbarui.');
    }

    public function updateLogo(Request $request): RedirectResponse
    {
        $request->validate([
            'logo_file' => ['required', 'image', 'mimes:png,jpg,jpeg,svg', 'max:2048'],
        ]);

        $path = $request->file('logo_file')->store('branding', 'public');
        AppConfig::setValue(AppConfigService::KEY_LOGO, ['path' => $path]);

        $this->auditLogService->log(
            action: 'UPDATE_APP_LOGO',
            detail: "Super Admin {$request->user()->name} mengunggah logo institusi baru."
        );

        return back()->with('success', 'Logo institusi berhasil diperbarui.');
    }
}
