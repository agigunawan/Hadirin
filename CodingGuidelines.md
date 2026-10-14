# Coding Standards & Guidelines
## Sistem Daftar Hadir Online Berbasis QR Code & E-Signature — "Hadir In"

* **Target Institusi:** Balai Besar Kekarantinaan Kesehatan (BBKK) Tanjung Priok, Kementerian Kesehatan RI
* **Referensi Utama:** [Hadirin.md (PRD v1.0)](file:///d:/Hadir%20In/Hadirin.md), [TechStack.md (Tech Stack v2.0)](file:///d:/Hadir%20In/TechStack.md), [Architecture.md (Architecture v1.0)](file:///d:/Hadir%20In/Architecture.md)
* **Target Stack:** PHP 8.3 | Laravel 11.x | MySQL 8.0+ | Livewire 3.x | Alpine.js 3.x | Tailwind CSS 3.x
* **Standar Acuan:** PSR-12 / PER Coding Style 2.0, SOLID Principles, OWASP Top 10
* **Versi Dokumen:** 1.0.0
* **Tanggal:** 13 Oktober 2026

---

## 1. Filosofi & Prinsip Utama Desain

Sistem **"Hadir In"** memproses data kehadiran kedinasan, koordinat geolocation, data perbankan (transport), dan tanda tangan digital untuk dokumen resmi negara (SPJ). Oleh karena itu, kode harus dirancang dengan prinsip **keterbacaan tinggi, integritas data tak terkompromikan, dan minim toleransi kesalahan**.

### 1.1 Prinsip Rekayasa (Engineering Principles)
1. **Strict & Type-Safe by Default:** Setiap berkas PHP wajib menggunakan deklarasi tipe data ketat (`declare(strict_types=1);`). Seluruh parameter fungsi, property kelas, dan nilai kembali (*return types*) wajib dideklarasikan secara eksplisit.
2. **Thin Controller, Rich Domain (Action/Service Pattern):** Controller hanya bertugas menerima HTTP request, meneruskan ke Form Request / Action / Service, dan mengembalikan HTTP response. Dilarang menempatkan query database langsung, manipulasi file kompleks, atau kalkulasi bisnis di dalam Controller.
3. **Fail-Fast & Early Return (Guard Clauses):** Hindari blok `if-else` bersarang (*arrow anti-pattern*). Periksa kondisi kegagalan atau validasi di awal fungsi dan segera lakukan `return` atau lempar `Exception`.
4. **Single Source of Truth:** Validasi bisnis dilakukan di level Form Request, divalidasi ulang di level Domain/Service, dan dikunci secara fisik di level skema database melalui relasi, indeks unik, dan tipe data kolom.
5. **Auditability & Traceability:** Setiap aksi kritis (persetujuan acara, penolakan, perubahan pimpinan/logo, pembubuhan presensi) wajib tercatat ke dalam `AuditLog` secara otomatis.
6. **Expressive & Self-Documenting:** Tulis kode yang menjelaskan dirinya sendiri melalui penamaan variabel, kelas, dan method yang deskriptif. Komentar hanya digunakan untuk menjelaskan alasan (*why*), bukan cara kerja kode (*what/how*).

---

## 2. Standar Bahasa PHP 8.3

Seluruh pengembang wajib mengoptimalkan fitur bawaan PHP 8.3 untuk menjaga keseragaman sintaksis:

### 2.1 Deklarasi Strict Types
Setiap berkas PHP wajib diawali dengan:
```php
<?php

declare(strict_types=1);

namespace App\Services;
```

### 2.2 Constructor Property Promotion & Readonly
Gunakan *Constructor Property Promotion* dan modifikator `readonly` untuk dependency injection serta kelas DTO (*Data Transfer Object*):
```php
// BENAR: Ringkas, jelas, dan immutability terjamin
final class ApproveEventAction
{
    public function __construct(
        private readonly QrTokenService $qrTokenService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function execute(Event $event, User $actor): Event
    {
        // ...
    }
}

// SALAH: Boilerplate usang PHP 7.x
class ApproveEventAction
{
    private $qrTokenService;
    public function __construct(QrTokenService $qrTokenService) {
        $this->qrTokenService = $qrTokenService;
    }
}
```

### 2.3 Typed Class Constants (PHP 8.3)
Konstanta kelas wajib memiliki tipe data eksplisit:
```php
final class EventConstant
{
    public const int MIN_SUBMISSION_DAYS_AHEAD = 1; // H-1
    public const string DEFAULT_REJECTION_REASON = 'Ruangan tidak tersedia pada jam tersebut.';
    public const int MAX_PHONE_LENGTH = 25;
}
```

### 2.4 PHP Enums & Match Expressions
Gunakan *Backed Enums* untuk status dan kategori sistem. Hindari *magic string* atau integer sembarangan:
```php
namespace App\Enums;

enum EventStatus: string
{
    case PENDING = 'PENDING';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';
    case CLOSED = 'CLOSED';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Verifikasi',
            self::APPROVED => 'Disetujui',
            self::REJECTED => 'Ditolak',
            self::CLOSED => 'Selesai / Ditutup',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::PENDING => 'yellow',
            self::APPROVED => 'emerald',
            self::REJECTED => 'rose',
            self::CLOSED => 'slate',
        };
    }
}
```

### 2.5 Null-Safe Operator & Null Coalescing
Gunakan operator modern `?->` dan `??` untuk menangani nilai opsional tanpa memicu `null pointer error`:
```php
// BENAR
$creatorName = $event->creator?->name ?? 'Sistem';

// SALAH
$creatorName = ($event->creator && $event->creator->name) ? $event->creator->name : 'Sistem';
```

### 2.6 Larangan Pemanggilan `env()` di Luar Konfigurasi
Dilarang memanggil `env()` secara langsung di dalam Controller, Model, Service, atau Blade View karena fungsi ini mengembalikan `null` saat konfigurasi di-cache (`php artisan config:cache`). Selalu gunakan helper `config()`:
```php
// BENAR
$disk = config('filesystems.default');

// SALAH (Berbahaya di production)
$disk = env('FILESYSTEM_DISK', 'local');
```

---

## 3. Konvensi Penamaan (Naming Conventions)

Konsistensi penamaan menjaga navigasi kode tetap intuitif antar anggota tim pengembang:

| Komponen | Format / Gaya | Contoh Kasus |
| :--- | :--- | :--- |
| **Classes & Enums** | `PascalCase` | `CollisionDetectionService`, `EventStatus`, `User` |
| **Methods & Functions**| `camelCase` | `isRoomAvailable()`, `generateQrToken()`, `store()` |
| **Variables & Properties** | `camelCase` | `$eventDate`, `$attendeeCount`, `$signatureData` |
| **Class Constants** | `UPPER_SNAKE_CASE` | `MAX_ATTENDEES`, `DEFAULT_CACHE_TTL` |
| **Enum Cases** | `UPPER_SNAKE_CASE` | `SUPER_ADMIN`, `ADMIN_TIM_KERJA`, `APPROVED` |
| **Database Tables** | `snake_case` (Plural) | `users`, `events`, `attendees`, `audit_logs` |
| **Database Columns** | `snake_case` | `event_date`, `nip_or_id`, `bank_account_no` |
| **Foreign Keys** | `singular_model_id` | `event_id`, `room_id`, `created_by` |
| **Web Routes (URI)** | `kebab-case` | `/presensi/{token}`, `/super-admin/event-approvals` |
| **Route Names** | `kebab.case` | `presensi.show`, `super-admin.events.approve` |
| **Blade Views** | `kebab-case.blade.php` | `form.blade.php`, `attendance-report.blade.php` |
| **Livewire Components** | `PascalCase` | `LiveCounter`, `EventStatusToggle` |
| **Form Requests** | `PascalCase` + `Request` | `StoreEventRequest`, `StoreAttendeeRequest` |
| **Database Migrations**| `YYYY_MM_DD_HHMMSS_create_table.php` | `2026_10_13_000001_create_events_table.php` |

---

## 4. Panduan Struktur & Lapisan Aplikasi

```text
HTTP Request
    │
    ▼
[ Route & Middleware Pipeline ]  <── Auth, RBAC, CheckEventStatus, Throttle
    │
    ▼
[ Form Request ]                 <── Validasi skema, pesan error, otorisasi awal
    │
    ▼
[ Controller ]                   <── Mengambil DTO / data valid, delegasi ke Service
    │
    ▼
[ Action / Service Layer ]       <── Business logic, Collision detection, DB Transaction
    │
    ▼
[ Eloquent Models & DB ]         <── Mutasi data, Index constraints, Relasi
    │
    ▼
[ AuditLog & Queue Worker ]      <── Append log, dispatch background tasks
    │
    ▼
Response (Blade / JSON / Stream)
```

### 4.1 Routing & Middleware
1. **Grouping Berdasarkan Modul & RBAC:** Rute harus dikelompokkan dengan `prefix`, `as`, dan `middleware` yang jelas.
2. **Explicit Binding:** Gunakan route model binding yang aman.

Contoh `routes/web.php`:
```php
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\SuperAdmin\EventApprovalController;

// Rute Publik (Tamu / Peserta) dengan Rate Limiting
Route::middleware(['throttle:30,1'])->group(function () {
    Route::get('/presensi/{token}', [PresensiController::class, 'show'])->name('presensi.show');
    Route::post('/presensi/{token}', [PresensiController::class, 'store'])->name('presensi.store');
});

// Rute Super Admin
Route::middleware(['auth', 'role:SUPER_ADMIN'])
    ->prefix('super-admin')
    ->as('super-admin.')
    ->group(function () {
        Route::get('/events', [EventApprovalController::class, 'index'])->name('events.index');
        Route::post('/events/{event}/approve', [EventApprovalController::class, 'approve'])->name('events.approve');
        Route::post('/events/{event}/reject', [EventApprovalController::class, 'reject'])->name('events.reject');
    });
```

### 4.2 Controllers (Thin Controller)
Controller tidak boleh memuat logika bisnis rumit. Pola standar:
```php
namespace App\Http\Controllers\SuperAdmin;

use App\Actions\Events\ApproveEventAction;
use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class EventApprovalController extends Controller
{
    public function approve(Event $event, ApproveEventAction $action, Request $request): RedirectResponse
    {
        $this->authorize('approve', $event);

        $action->execute($event, $request->user());

        return redirect()
            ->route('super-admin.events.index')
            ->with('success', 'Agenda berhasil disetujui dan token QR telah diterbitkan.');
    }
}
```

### 4.3 Form Requests & Validasi
1. Pisahkan validasi ke berkas `FormRequest` terpisah.
2. Gunakan method `prepareForValidation()` untuk sanitasi teks (misal: menghapus spasi ekstra atau tag HTML).
3. Selalu sediakan pesan error dalam Bahasa Indonesia yang ramah pengguna.

Contoh `StoreAttendeeRequest.php`:
```php
namespace App\Http\Requests;

use App\Enums\Gender;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreAttendeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Validasi status agenda ditangani oleh middleware / service
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'full_name' => strip_tags(trim((string) $this->input('full_name'))),
            'agency' => strip_tags(trim((string) $this->input('agency'))),
            'nip_or_id' => preg_replace('/[^0-9]/', '', (string) $this->input('nip_or_id')),
            'phone' => preg_replace('/[^0-9+]/', '', (string) $this->input('phone')),
        ]);
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:150'],
            'agency' => ['required', 'string', 'max:150'],
            'is_internal' => ['required', 'boolean'],
            'gender' => ['required', Rule::enum(Gender::class)],
            'nip_or_id' => ['required', 'string', 'min:8', 'max:20'],
            'position' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:25'],
            'address' => ['required', 'string', 'max:255'],
            'has_transport' => ['sometimes', 'boolean'],
            'bank_name' => ['required_if:has_transport,true', 'nullable', 'string', 'max:50'],
            'bank_account_no' => ['required_if:has_transport,true', 'nullable', 'string', 'max:30'],
            'bank_account_name' => ['required_if:has_transport,true', 'nullable', 'string', 'max:100'],
            'coordinates' => ['nullable', 'string', 'regex:/^-?\d+(\.\d+)?, -?\d+(\.\d+)?$/'],
            'signature_data' => ['required', 'string', 'starts_with:data:image/png;base64,'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Nama lengkap wajib diisi beserta gelar kedinasan.',
            'nip_or_id.required' => 'NIP atau NIK pegawai wajib diisi.',
            'bank_name.required_if' => 'Nama Bank wajib diisi apabila memilih penggantian transport.',
            'signature_data.required' => 'Tanda tangan digital pada kanvas wajib dibubuhkan.',
        ];
    }
}
```

### 4.4 Services & Actions (Domain Layer)
1. **Actions:** Kelas spesifik dengan satu tanggung jawab (*single responsibility*). Memiliki satu method utama: `execute()` atau `__invoke()`.
2. **Services:** Kelas yang mengelompokkan logika domain serumpun (misal: `CollisionDetectionService`, `QrTokenService`).

Contoh `CollisionDetectionService.php`:
```php
namespace App\Services;

use App\Enums\EventStatus;
use App\Models\Event;
use Carbon\CarbonInterface;

final class CollisionDetectionService
{
    /**
     * Memeriksa apakah terjadi irisan waktu rapat pada ruangan yang sama.
     */
    public function hasCollision(
        string $roomId,
        CarbonInterface $date,
        int $startMinutes,
        int $endMinutes,
        ?string $ignoreEventId = null,
    ): bool {
        return Event::query()
            ->where('room_id', $roomId)
            ->whereDate('event_date', $date->toDateString())
            ->whereIn('status', [EventStatus::APPROVED, EventStatus::PENDING])
            ->when($ignoreEventId, fn ($query) => $query->where('id', '!=', $ignoreEventId))
            ->where(function ($query) use ($startMinutes, $endMinutes) {
                $query->where('start_minutes', '<', $endMinutes)
                    ->where('end_minutes', '>', $startMinutes);
            })
            ->exists();
    }
}
```

### 4.5 Eloquent Models & Integrity
1. **Mass Assignment:** Wajib mendeklarasikan `$fillable` secara eksplisit. Jangan pernah menggunakan `$guarded = []` pada model yang menerima input pengguna.
2. **Type Casting:** Deklarasikan `$casts` untuk enum, boolean, datetime, dan format JSON.
3. **Primary Key UUID:** Gunakan trait `HasUuids` bawaan Laravel 11.
4. **Relasi:** Selalu tulis *return type* eksplisit (`BelongsTo`, `HasMany`, `HasOne`).

Contoh `Event.php`:
```php
namespace App\Models;

use App\Enums\EventStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Event extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'room_id',
        'event_date',
        'start_minutes',
        'end_minutes',
        'work_unit',
        'status',
        'rejection_note',
        'qr_token',
        'config_fields',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'start_minutes' => 'integer',
            'end_minutes' => 'integer',
            'status' => EventStatus::class,
            'config_fields' => 'array',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(Attendee::class);
    }
}
```

---

## 5. Transaksi Database, Integritas & Concurrency

### 5.1 Database Transactions
Setiap operasi yang melibatkan lebih dari satu tabel atau langkah kritikal wajib dibungkus dalam `DB::transaction()`:
```php
use Illuminate\Support\Facades\DB;

public function submitPresensi(Event $event, array $validatedData, array $evaluationData): Attendee
{
    return DB::transaction(function () use ($event, $validatedData, $evaluationData) {
        // 1. Simpan data peserta dan tanda tangan
        $attendee = $event->attendees()->create($validatedData);

        // 2. Simpan evaluasi kepuasan jika tersedia
        if (!empty($evaluationData['rating'])) {
            $attendee->evaluation()->create($evaluationData);
        }

        // 3. Catat audit log transaksi
        $this->auditLogService->log(
            action: 'SUBMIT_KEHADIRAN',
            detail: "Peserta {$attendee->full_name} ({$attendee->nip_or_id}) presensi pada {$event->title}",
            username: $attendee->nip_or_id
        );

        return $attendee;
    });
}
```

### 5.2 Atomic Locks untuk Pencegahan Race Condition
Gunakan *Cache Lock* (Redis atau Database lock) saat memproses transaksi yang rentan bentrok serentak:
```php
use Illuminate\Support\Facades\Cache;

$lock = Cache::lock("room_booking:{$roomId}:{$dateString}", 10);

if ($lock->get()) {
    try {
        // Validasi dan simpan agenda
    } finally {
        $lock->release();
    }
} else {
    throw new RoomBookingCollisionException('Ruangan sedang diproses oleh pemohon lain. Coba beberapa saat lagi.');
}
```

### 5.3 Pencegahan N+1 Query
Selalu gunakan *Eager Loading* (`with(...)`) saat menampilkan daftar relasi:
```php
// BENAR: Hanya mengeksekusi 2 query
$events = Event::with(['room', 'creator'])
    ->whereDate('event_date', today())
    ->get();

// SALAH: Memicu N+1 query ke database
$events = Event::whereDate('event_date', today())->get();
foreach ($events as $event) {
    echo $event->room->name; // Memicu query di setiap perulangan
}
```

---

## 6. Keamanan & Penanganan Error

### 6.1 Sanitasi & XSS Prevention
* Di dalam Blade View, selalu gunakan interpolasi aman `{{ $data }}`.
* Hindari sintaks `{!! $data !!}` kecuali untuk merender gambar SVG QR Code yang dihasilkan langsung oleh library terpercaya `SimpleSoftwareIO\QrCode`.
* Semua masukan teks wajib dibersihkan dari tag HTML sebelum disimpan.

### 6.2 CSRF Protection
* Seluruh form HTML wajib menyertakan direktif `@csrf`.
* Permintaan AJAX/Fetch wajib melampirkan header `X-CSRF-TOKEN` yang diambil dari meta tag:
  `<meta name="csrf-token" content="{{ csrf_token() }}">`.

### 6.3 Penanganan Exception Terstruktur
* Jangan pernah membuat blok `catch` kosong (*swallowing exceptions*).
* Gunakan Custom Exceptions untuk membedakan kesalahan bisnis (*Domain Exception*) dengan kegagalan teknis (*System Exception*):
```php
namespace App\Exceptions;

use RuntimeException;

final class RoomCollisionException extends RuntimeException
{
    public static function forSchedule(string $roomName, string $timeRange): self
    {
        return new self("Ruang rapat '{$roomName}' telah terisi pada rentang waktu {$timeRange}.");
    }
}
```
* Log setiap *unexpected exception* dengan context yang informatif:
```php
use Illuminate\Support\Facades\Log;

try {
    // Operasi ekspor Excel atau render tanda tangan
} catch (\Throwable $e) {
    Log::error('Gagal memproses berkas SPJ Excel', [
        'event_id' => $event->id,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);

    throw $e;
}
```

---

## 7. Frontend Guidelines (Blade, Livewire, Alpine & Tailwind)

### 7.1 Standar Blade & Komponen UI
* Gunakan komponen Blade anonim (`<x-button>`, `<x-input>`, `<x-card>`) untuk konsistensi elemen visual.
* Hindari logika percabangan bisnis yang rumit di dalam view; gunakan view model atau helper presenter.

### 7.2 Standar Tailwind CSS
* Utamakan desain **Mobile-First**: Rancang tampilan layar ponsel terlebih dahulu, kemudian gunakan prefix responsif (`md:`, `lg:`) untuk layar tablet dan monitor proyektor (16:9).
* Pastikan standar aksesibilitas **WCAG 2.1 AA**:
  * Touch target minimal **44 × 44 piksel** untuk tombol pada ponsel.
  * Rasio kontras teks minimal **4.5:1** terhadap latar belakang.

### 7.3 Kanvas E-Signature (Mobile Touch-Friendly)
* Kanvas wajib mengaktifkan pencegahan *scroll window* (`touch-action: none;`) saat pengguna sedang menandatangani.
* Sediakan tombol eksplisit "Bersihkan Tanda Tangan" (*Clear Canvas*).
* Validasi kanvas tidak boleh kosong sebelum form dikirimkan (*client-side & server-side validation*).

### 7.4 Livewire 3 Guidelines
* Komponen reaktif (seperti *Live Counter* kehadiran) harus membatasi payload data seminimal mungkin.
* Gunakan modifier `wire:model.live.debounce.300ms` pada input pencarian untuk menghindari lonjakan HTTP request.

---

## 8. Standar Git, Commit & Dokumentasi

### 8.1 Format Pesan Commit (Conventional Commits)
Setiap commit harus mengikuti pola standar:
```text
<tipe>(<lingkup opsional>): <deskripsi singkat dalam bahasa aktif>

[penjelasan opsional mengenai latar belakang dan dampak perubahan]
```

Daftar tipe commit:
* `feat`: Fitur baru (misal: `feat(presensi): tambahkan validasi koordinat GPS`).
* `fix`: Perbaikan bug (misal: `fix(collision): cegah bentrok waktu pada ruangan yang sama`).
* `refactor`: Perubahan struktur kode tanpa mengubah fungsionalitas.
* `perf`: Peningkatan performa kode atau query.
* `test`: Penambahan atau penyesuaian unit/feature test.
* `docs`: Pembaruan dokumentasi proyek.
* `chore`: Pemeliharaan konfigurasi build tool atau dependensi.

### 8.2 Larangan File dalam Version Control (`.gitignore`)
Berkas-berkas berikut dilarang keras masuk ke dalam Git repository:
* File kredensial environment: `.env`, `.env.production`.
* Direktori dependensi: `/vendor/`, `/node_modules/`.
* Direktori asset hasil build: `/public/build/`, `/public/hot`.
* File penyimpanan upload publik: `/storage/*.key`, `/storage/app/public/signatures/*`.

---

## 9. Checklist Kualitas Kode (Code Review Checklist)

Sebelum *Pull Request* atau penggabungan kode disetujui, pastikan seluruh kriteria berikut terpenuhi:

- [ ] **Strict Typing:** `declare(strict_types=1);` terpasang di baris teratas semua file PHP.
- [ ] **No Fat Controller:** Controller hanya memiliki logika routing dan delegasi; bisnis logic berada di Action/Service.
- [ ] **Form Request:** Seluruh parameter input divalidasi via Form Request dengan sanitasi `strip_tags` dan pesan berbahasa Indonesia.
- [ ] **No Raw `env()`:** Tidak ada pemanggilan `env()` selain di file `config/*.php`.
- [ ] **Database Integrity:** Tabel memiliki indeks yang tepat, foreign key terpasang, dan mutasi multi-tabel dibungkus `DB::transaction()`.
- [ ] **No N+1 Queries:** Seluruh pembacaan relasi list menggunakan `with(...)`.
- [ ] **Audit Trail:** Aksi penting memanggil `AuditLogService`.
- [ ] **Mobile Touch-Target:** Elemen interaktif pada form mobile memiliki ukuran minimal 44px.
- [ ] **XSS Protected:** Data dinamis dipanggil dengan `{{ }}` dan bukan `{!! !!}`.
- [ ] **Code Formatting:** Lolos linting Laravel Pint (`./vendor/bin/pint`).
