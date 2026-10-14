<?php

declare(strict_types=1);

use App\Http\Controllers\AdminTimKerja\AttendeeMonitorController;
use App\Http\Controllers\AdminTimKerja\EventController as TimKerjaEventController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DisplayController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\SuperAdmin\AppConfigController;
use App\Http\Controllers\SuperAdmin\AuditLogController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\EventApprovalController;
use App\Http\Controllers\SuperAdmin\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Sistem Presensi "Hadir In" BBKK Tanjung Priok
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => redirect()->route('login'));

// Autentikasi Sistem
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Display Mode Proyektor 16:9
Route::get('/display/{token}', [DisplayController::class, 'show'])->name('display.show');

// Alur Presensi Peserta & Tamu Kedinasan (Mobile Web)
Route::prefix('presensi/{token}')->as('presensi.')->group(function () {
    Route::middleware(['event.status'])->group(function () {
        Route::get('/', [PresensiController::class, 'show'])->name('show');
        Route::post('/', [PresensiController::class, 'store'])
            ->middleware(['throttle:30,1'])
            ->name('store');
    });

    Route::get('/survey/{attendee}', [PresensiController::class, 'survey'])->name('survey');
    Route::post('/survey/{attendee}', [PresensiController::class, 'storeSurvey'])->name('survey.store');
    Route::get('/success/{attendee}', [PresensiController::class, 'success'])->name('success');
});

// Modul Super Admin (Pengelola Sistem)
Route::middleware(['auth', 'role:SUPER_ADMIN'])
    ->prefix('super-admin')
    ->as('super-admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Manajemen & Verifikasi Agenda
        Route::get('/events', [EventApprovalController::class, 'index'])->name('events.index');
        Route::post('/events/{event}/approve', [EventApprovalController::class, 'approve'])->name('events.approve');
        Route::post('/events/{event}/reject', [EventApprovalController::class, 'reject'])->name('events.reject');
        Route::post('/events/{event}/toggle-status', [EventApprovalController::class, 'toggleStatus'])->name('events.toggle-status');
        Route::post('/events/{event}/pimpinan', [TimKerjaEventController::class, 'updatePimpinan'])->name('events.pimpinan');
        Route::get('/events/{event}/export', [ReportController::class, 'exportSpj'])->name('events.export');
        Route::get('/events/{event}/export-pdf', [ReportController::class, 'exportPdf'])->name('events.export-pdf');

        // Monitoring Audit Trail
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

        // Pengaturan Pimpinan & Logo
        Route::get('/config', [AppConfigController::class, 'index'])->name('config.index');
        Route::post('/config/pimpinan', [AppConfigController::class, 'updatePimpinan'])->name('config.pimpinan');
        Route::post('/config/logo', [AppConfigController::class, 'updateLogo'])->name('config.logo');
    });

// Modul Admin Tim Kerja
Route::middleware(['auth', 'role:ADMIN_TIM_KERJA,SUPER_ADMIN'])
    ->prefix('admin-tim-kerja')
    ->as('admin-tim-kerja.')
    ->group(function () {
        Route::get('/events', [TimKerjaEventController::class, 'index'])->name('events.index');
        Route::get('/events/create', [TimKerjaEventController::class, 'create'])->name('events.create');
        Route::post('/events', [TimKerjaEventController::class, 'store'])->name('events.store');
        Route::get('/events/{event}/edit', [TimKerjaEventController::class, 'edit'])->name('events.edit');
        Route::put('/events/{event}', [TimKerjaEventController::class, 'update'])->name('events.update');
        Route::delete('/events/{event}', [TimKerjaEventController::class, 'destroy'])->name('events.destroy');

        Route::get('/events/{event}/monitor', [AttendeeMonitorController::class, 'show'])->name('events.monitor');
        Route::get('/events/{event}/export', [AttendeeMonitorController::class, 'export'])->name('events.export');
        Route::get('/events/{event}/export-pdf', [AttendeeMonitorController::class, 'exportPdf'])->name('events.export-pdf');
        Route::post('/events/{event}/pimpinan', [TimKerjaEventController::class, 'updatePimpinan'])->name('events.pimpinan');
    });
