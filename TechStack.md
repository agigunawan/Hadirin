# Technology Stack Document
## Sistem Daftar Hadir Online Berbasis QR Code & E-Signature — "Hadir In"

* **Referensi PRD:** Hadirin.md v1.0.0
* **Referensi Standar Koding:** [CodingGuidelines.md](file:///d:/Hadir%20In/CodingGuidelines.md)
* **Referensi Kode Eksisting:** Google Apps Script (`Code.gs` by @wahyudi)
* **Versi Dokumen:** 2.0.0
* **Tanggal:** 13 Oktober 2026

---

## 1. Ringkasan Arsitektur

```text
┌──────────────────────────────────────────────────────────────────┐
│                        CLIENT (Browser)                         │
│  ┌──────────────┐  ┌──────────────────┐  ┌───────────────────┐  │
│  │  Blade Views  │  │  Alpine.js /     │  │  Signature Pad    │  │
│  │  + Tailwind   │  │  Livewire 3.x    │  │  + Geolocation API│  │
│  └──────┬───────┘  └───────┬──────────┘  └────────┬──────────┘  │
└─────────┼──────────────────┼──────────────────────┼─────────────┘
          │                  │                      │
          ▼                  ▼                      ▼
┌──────────────────────────────────────────────────────────────────┐
│                     SERVER (Laravel 11.x)                       │
│  ┌────────────┐  ┌───────────┐  ┌──────────┐  ┌─────────────┐  │
│  │  Routes &   │  │  Eloquent │  │  Queue   │  │  Middleware  │  │
│  │  Controller │  │  ORM      │  │  Worker  │  │  (Auth/RBAC) │  │
│  ├────────────┴──┴───────────┴──┴──────────┴──┴─────────────┤  │
│  │  Services: CollisionDetect, QrToken, ExcelExport, Audit  │  │
│  └──────────────────────────────┬───────────────────────────┘  │
└─────────────────────────────────┼──────────────────────────────┘
                                  │
                                  ▼
┌──────────────────────────────────────────────────────────────────┐
│                     DATABASE & STORAGE                           │
│  ┌──────────────────────────┐      ┌─────────────────────────┐  │
│  │       MySQL 8.0+         │      │      Redis & Storage    │  │
│  │ • users      • rooms     │      │ • Redis (Cache/Session/ │  │
│  │ • events     • attendees │      │   Queue & Atomic Locks) │  │
│  │ • evaluations• audit_logs│      │ • Local/S3 Storage      │  │
│  │ • app_configs            │      │   (Signatures & Logos)  │  │
│  └──────────────────────────┘      └─────────────────────────┘  │
└──────────────────────────────────────────────────────────────────┘
```

---

## 2. Analisis & Rangkuman Sistem Eksisting (Google Apps Script)

Sistem sebelumnya berjalan di atas Google Apps Script (GAS) dengan Google Sheets sebagai basis data dan Google Drive sebagai penyimpanan berkas. Berikut rangkuman modul dan fungsi utamanya:

### 2.1 Fitur Inti dalam Kode GAS (`Code.gs`)

1. **Struktur Data (Google Sheets):**
   * **`Users`:** Autentikasi pengelola (`Username`, `PasswordHash` SHA-256, `Role`, `FullName`, `CreatedAt`).
   * **`Kegiatan`:** Manajemen agenda (`ID`, `Nama`, `Tempat`, `Waktu`, `Jam`, `Status`, `ConfigFields`, `CreatedAt`). Mendukung self-healing schema.
   * **`Peserta`:** Rekapitulasi kehadiran (`ID`, `KegiatanID`, `Nama`, `NIP`, `Jabatan`, `Gender`, `HP`, `Instansi`, `Alamat`, `Rekening`, `SignatureData`, `Koordinat`, `Timestamp`).
   * **`AuditLog`:** Pencatatan log aktivitas sistem (`Timestamp`, `User`, `Action`, `Detail`).
   * **`AppConfig`:** Konfigurasi aplikasi key-value, khususnya `INFO_PIMPINAN` (profil dan TTD digital pimpinan untuk berkas resmi).

2. **Keamanan & Manajemen Sesi:**
   * Password di-hash menggunakan SHA-256 (`Utilities.computeDigest`).
   * Sesi berbasis token UUID dengan masa aktif 8 jam di `CacheService`.
   * Perlindungan brute-force (*rate limiting*): batas 5 kali gagal login, akun dikunci 15 menit.
   * Sanitasi input teks (`sanitizeInput`) untuk menghapus tag HTML (*XSS prevention*).

3. **Publik (Kiosk Mode / Tamu):**
   * `getPublicKegiatan`: Menampilkan daftar kegiatan yang berstatus aktif (`Status !== 'Tutup'`).
   * `submitKehadiran`: Menyimpan data presensi mandiri dengan proteksi *concurrency lock* (`LockService` hingga 10 detik). Mendukung penangkapan **Koordinat GPS** peserta, data nomor kontak (HP), alamat, serta tanda tangan kanvas.

4. **Operasional Admin:**
   * CRUD Agenda dengan pencegahan bentrok ruangan & waktu (`Tempat` & `Waktu`).
   * Konfigurasi dinamis isian formulir per kegiatan (`ConfigFields`).
   * Toggle status agenda langsung (Buka / Tutup).
   * Rescue script (`perbaikiLoginAdmin`): Fungsi darurat untuk me-reset kredensial admin dan membersihkan cache kegagalan login.

---

## 3. Matriks Transformasi: Google Apps Script → Laravel & MySQL

| Aspek / Fungsi GAS | Implementasi di Google Apps Script | Transformasi ke Laravel 11 + MySQL | Keunggulan Laravel |
| :--- | :--- | :--- | :--- |
| **Penyimpanan Data** | Google Sheets (baris spreadsheet) | MySQL 8.0+ Relational Database | Dukungan ACID transaction, index performa tinggi, foreign key integrity |
| **Hashing Password** | SHA-256 murni (`hashPassword`) | `bcrypt` / `Argon2id` via `Hash::make()` | Jauh lebih tahan serangan brute-force dan rainbow table |
| **Autentikasi & Sesi** | UUID manual di `CacheService` | Laravel Session / Breeze / Sanctum | Proteksi CSRF bawaan, HttpOnly cookie, session rotation otomatis |
| **Rate Limiter** | Counter manual di `CacheService` | Laravel `RateLimiter` (`throttle:5,15`) | Terintegrasi di level routing/middleware secara deklaratif |
| **Concurrency Control** | `LockService.getScriptLock()` | MySQL Transactions (`DB::transaction`) & Redis Atomic Locks (`Cache::lock`) | Bebas bottleneck batas kuota Google Apps Script |
| **Audit Logging** | `logAction()` append ke sheet `AuditLog` | Eloquent `AuditLog` model / Spatie Activitylog | Query terindeks, filter waktu instan, paginasi cepat |
| **Konfigurasi Pimpinan** | Key-Value `INFO_PIMPINAN` di `AppConfig` | Tabel `app_configs` / Settings Service | Tipe data JSON terstruktur, kemudahan upload logo & TTD via storage |
| **Logo Institusi** | Google Drive Blob (`getLogoBase64`) | Laravel Storage (`storage/app/public/`) | Response gambar ter-cache Nginx, tanpa latensi Drive API |
| **Deteksi Bentrok** | Perulangan linear `for` seluruh baris | Eloquent Query Scope dengan index waktu | Kompleksitas $O(1)$ query via index DB dibanding $O(N)$ scan |
| **Koordinat Geolocation** | Kolom string `Koordinat` | Kolom `coordinates` / `latitude` & `longitude` | Validasi format presisi dan potensi integrasi peta/radius |
| **Config Fields Dinamis** | String CSV (`ConfigFields`) | Kolom `JSON` pada tabel `events` | Fleksibilitas konfigurasi form presensi per kegiatan (aktif/wajib) |
| **Rescue Script** | Fungsi `perbaikiLoginAdmin()` | Artisan Command: `php artisan admin:reset` | Eksekusi aman melalui CLI server, tanpa ubah source code |

---

## 4. Technology Stack Detail

### 4.1 Backend Framework

| Komponen | Teknologi | Versi | Justifikasi |
| :--- | :--- | :--- | :--- |
| **Framework** | Laravel | 11.x | Full-stack PHP framework dengan arsitektur MVC matang, Eloquent ORM, built-in rate limiter, queue, dan security middleware. |
| **Bahasa** | PHP | 8.3 | Minimum requirement Laravel 11 (8.2+), dikunci pada PHP 8.3. Fitur modern: Typed class constants, dynamic class constant fetch, `#[Override]` attribute, performa opcache lebih cepat. |
| **Web Server** | Nginx | Latest | Reverse proxy dengan performa concurrency tinggi untuk PHP-FPM. |
| **Process Manager** | PHP-FPM | 8.3 | Eksekusi PHP terisolasi dan efisien melayani lonjakan presensi massal. |

### 4.2 Database

| Komponen | Teknologi | Versi | Justifikasi |
| :--- | :--- | :--- | :--- |
| **RDBMS** | MySQL | 8.0+ | Menggantikan Google Sheets. Menjamin integritas referensial, constraint unik, serta tipe data JSON. |
| **ORM** | Eloquent ORM | (built-in) | Abstraksi query database, relasi antar entitas, accessor/mutator, dan model events. |
| **Migration & Seeder**| Laravel Migrations | (built-in) | Kontrol versi skema database terpusat dan konsisten di setiap environment. |

### 4.3 Frontend

| Komponen | Teknologi | Versi | Justifikasi |
| :--- | :--- | :--- | :--- |
| **Template Engine** | Blade | (built-in) | Server-side rendering cepat, komponen modular, dan ramah SEO/perangkat mobile. |
| **CSS Framework** | Tailwind CSS | 3.x | Utility-first CSS responsif mobile-first sesuai standar ukuran sentuh dinas (WCAG 2.1 AA). |
| **Reactive UI** | Livewire | 3.x | Digunakan untuk Live Counter (FR-06), pemantauan kehadiran, dan toggle status instan tanpa refresh. |
| **Client Interaction**| Alpine.js | 3.x | Komponen ringan untuk toggle rekening bank, GPS fetching, dan modal dialog. |
| **E-Signature** | Signature Pad | 5.x | Kanvas tanda tangan digital touch-friendly dengan penguncian scroll layar (*scroll-lock*). |
| **QR Code Generator** | Simple QrCode | 4.x | Pembuatan QR Code SVG/PNG tajam untuk monitor Display Mode 16:9. |
| **Build Tool** | Vite | 5.x | Asset bundler ultra-cepat dengan Hot Module Replacement (HMR). |

### 4.4 Autentikasi, Autorisasi & Keamanan

| Komponen | Teknologi | Justifikasi |
| :--- | :--- | :--- |
| **Authentication** | Laravel Breeze | Sistem login terstandar dengan perlindungan brute-force bawaan. |
| **Authorization (RBAC)**| Spatie Laravel Permission | Pengelolaan peran: `SUPER_ADMIN` dan `ADMIN_TIM_KERJA`. |
| **Rate Limiter** | Laravel RateLimiter | Menggantikan logika manual GAS: batas 5 percobaan gagal dengan lockout 15 menit. |
| **Security Headers** | Secure Headers Middleware | Anti-XSS, CSRF Token protection, dan frame restrictions. |

### 4.5 Laporan, File Storage & Audit

| Komponen | Teknologi | Versi | Justifikasi |
| :--- | :--- | :--- | :--- |
| **Excel Export** | Laravel Excel (Maatwebsite) | 3.x | Ekspor data presensi ke format .xlsx lengkap dengan kop dinas dan TTD pimpinan. |
| **Audit Logging** | Custom Model / Spatie Activitylog | - | Migrasi langsung fitur `AuditLog` GAS untuk mencatat seluruh aksi penting sistem. |
| **File Storage** | Laravel Storage (Local / S3) | - | Penyimpanan gambar logo institusi, aset TTD pimpinan, dan berkas tanda tangan peserta. |

---

## 5. Skema Database MySQL Terkini

### 5.1 Entity Relationship Diagram (ERD)

```mermaid
erDiagram
    users ||--o{ events : "creates"
    users ||--o{ audit_logs : "triggers"
    rooms ||--o{ events : "hosts"
    events ||--o{ attendees : "registers"
    attendees ||--o| evaluations : "submits"

    users {
        char(36) id PK
        varchar(50) username UK
        varchar(255) password
        varchar(100) name
        enum role "SUPER_ADMIN, ADMIN_TIM_KERJA"
        varchar(100) work_unit "nullable"
        timestamp created_at
        timestamp updated_at
    }

    rooms {
        char(36) id PK
        varchar(100) name UK
        int capacity "nullable"
        boolean is_active "default true"
        timestamp created_at
        timestamp updated_at
    }

    events {
        char(36) id PK
        varchar(255) title
        char(36) room_id FK
        date event_date
        int start_minutes
        int end_minutes
        varchar(100) work_unit
        enum status "PENDING, APPROVED, REJECTED, CLOSED"
        text rejection_note "nullable"
        char(36) qr_token UK
        json config_fields "Konfigurasi isian form dinamis"
        char(36) created_by FK
        timestamp created_at
        timestamp updated_at
    }

    attendees {
        char(36) id PK
        char(36) event_id FK
        varchar(150) full_name
        varchar(150) agency
        boolean is_internal
        enum gender "LAKI_LAKI, PEREMPUAN"
        varchar(20) nip_or_id
        varchar(100) position
        varchar(25) phone "No HP / WhatsApp"
        varchar(255) address "Alamat asal"
        varchar(50) bank_name "nullable"
        varchar(30) bank_account_no "nullable"
        varchar(100) bank_account_name "nullable"
        varchar(100) coordinates "GPS Lat,Long"
        mediumtext signature_data "Base64 PNG"
        timestamp created_at
    }

    evaluations {
        char(36) id PK
        char(36) attendee_id FK-UK
        enum rating "SANGAT_BAIK, BAIK, CUKUP, KURANG"
        text feedback "nullable"
        timestamp created_at
    }

    audit_logs {
        bigint id PK
        timestamp timestamp
        varchar(50) username "User atau Guest"
        varchar(50) action "LOGIN, ADD_KEGIATAN, SUBMIT_KEHADIRAN, etc."
        text detail
        varchar(45) ip_address "nullable"
    }

    app_configs {
        int id PK
        varchar(50) config_key UK "INFO_PIMPINAN, APP_LOGO, etc."
        json config_value "Payload profil & TTD Pimpinan"
        timestamp updated_at
    }
```

### 5.2 Strategi Index & Integrity Constraints

| Tabel | Kolom / Index | Tipe | Tujuan |
| :--- | :--- | :--- | :--- |
| `users` | `username` | UNIQUE | Mencegah duplikasi akun login |
| `rooms` | `name` | UNIQUE | Memastikan nama ruang rapat tidak ganda |
| `events` | `qr_token` | UNIQUE | Token akses presensi unik |
| `events` | `(room_id, event_date, start_minutes, end_minutes)` | INDEX | Optimasi deteksi bentrok jadwal (*collision detection*) |
| `attendees` | `(event_id, nip_or_id)` | UNIQUE | Mencegah presensi ganda peserta pada sesi yang sama |
| `evaluations` | `attendee_id` | UNIQUE | Satu evaluasi kepuasan per peserta |
| `audit_logs` | `(timestamp, action)` | INDEX | Mempercepat pencarian log investigasi |
| `app_configs` | `config_key` | UNIQUE | Identifikasi entri pengaturan sistem |

---

## 6. Pemetaan Modul Kode GAS ke Komponen Laravel

```text
Google Apps Script (Lama)              Laravel 11 (Baru)
─────────────────────────────          ─────────────────────────────────────────
doGet()                        ─────>  routes/web.php (Routing & Blade Layouts)
getLogoBase64()                ─────>  Storage::disk('public')->url('logo.png')
getInfoPimpinanData()          ─────>  AppConfigService::getPimpinanProfile()
saveInfoPimpinanData()         ─────>  AppConfigController::updatePimpinan()
setupDatabase()                ─────>  php artisan migrate --seed
hashPassword()                 ─────>  Hash::make() / Hash::check()
generateSessionToken()         ─────>  Laravel Session Management
validateSession()              ─────>  auth & auth:sanctum middleware
checkLoginRateLimit()          ─────>  RateLimiter::attempt()
logAction()                    ─────>  AuditLogService::log() / Spatie Activitylog
getPublicKegiatan()            ─────>  PublicEventController::index()
submitKehadiran()              ─────>  PresensiController::store() (DB::transaction)
getKegiatan() / saveKegiatan() ─────>  EventController::index() / store()
toggleStatusKegiatan()         ─────>  Livewire Toggle / EventController::toggleStatus()
deleteKegiatan()               ─────>  EventController::destroy()
getPeserta() / deletePeserta() ─────>  AttendeeController::index() / destroy()
perbaikiLoginAdmin()           ─────>  php artisan make:command ResetAdminCommand
```

---

## 7. Struktur Direktori Proyek Laravel

```text
hadir-in/
├── app/
│   ├── Console/Commands/
│   │   └── ResetAdminCommand.php           # Pengganti fungsi perbaikiLoginAdmin()
│   ├── Enums/
│   │   ├── EventStatus.php                 # PENDING, APPROVED, REJECTED, CLOSED
│   │   ├── Gender.php                      # LAKI_LAKI, PEREMPUAN
│   │   ├── IkmRating.php                   # SANGAT_BAIK, BAIK, CUKUP, KURANG
│   │   └── UserRole.php                    # SUPER_ADMIN, ADMIN_TIM_KERJA
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/LoginController.php
│   │   │   ├── SuperAdmin/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── EventApprovalController.php
│   │   │   │   ├── AppConfigController.php  # Pengaturan Pimpinan & TTD
│   │   │   │   ├── AuditLogController.php   # Monitoring Audit Log
│   │   │   │   └── ReportController.php
│   │   │   ├── AdminTimKerja/
│   │   │   │   ├── EventController.php
│   │   │   │   └── AttendeeMonitorController.php
│   │   │   ├── DisplayController.php        # /display/{token}
│   │   │   └── PresensiController.php       # /presensi/{token}
│   │   ├── Middleware/
│   │   │   ├── CheckRole.php
│   │   │   └── CheckEventStatus.php
│   │   └── Requests/
│   │       ├── StoreEventRequest.php
│   │       ├── StoreAttendeeRequest.php
│   │       └── StoreEvaluationRequest.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Room.php
│   │   ├── Event.php
│   │   ├── Attendee.php
│   │   ├── Evaluation.php
│   │   ├── AuditLog.php                     # Log aktivitas sistem
│   │   └── AppConfig.php                    # Pengaturan pimpinan & logo
│   ├── Services/
│   │   ├── CollisionDetectionService.php    # Validasi bentrok ruangan
│   │   ├── QrTokenService.php              # Generator token presensi
│   │   ├── ExcelExportService.php          # Generator laporan SPJ Excel
│   │   └── AuditLogService.php             # Pencatat aktivitas terpusat
│   └── Livewire/
│       ├── LiveCounter.php                  # Real-time counter kehadiran
│       └── EventStatusToggle.php            # Toggle Buka/Tutup agenda
├── database/
│   ├── migrations/
│   │   ├── 0001_create_users_table.php
│   │   ├── 0002_create_rooms_table.php
│   │   ├── 0003_create_events_table.php
│   │   ├── 0004_create_attendees_table.php
│   │   ├── 0005_create_evaluations_table.php
│   │   ├── 0006_create_audit_logs_table.php
│   │   └── 0007_create_app_configs_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── UserSeeder.php
│       ├── RoomSeeder.php
│       └── AppConfigSeeder.php
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   ├── app.blade.php
│   │   │   └── display.blade.php            # Tampilan proyektor 16:9
│   │   ├── presensi/
│   │   │   ├── form.blade.php               # Form identitas + Geolocation GPS
│   │   │   ├── signature.blade.php          # Kanvas E-Signature
│   │   │   └── survey.blade.php             # Form evaluasi IKM
│   │   └── super-admin/
│   │       ├── config/index.blade.php       # Form Info Pimpinan & TTD
│   │       └── audit-logs/index.blade.php   # Tabel Audit Log
│   └── js/
│       ├── app.js
│       ├── signature.js                     # Inisialisasi Signature Pad
│       └── geolocation.js                   # Penangkapan koordinat browser
├── routes/
│   ├── web.php
│   └── console.php
└── ...
```

---

## 8. Daftar Dependensi Paket

### 8.1 Composer (PHP)
* `laravel/framework` (^11.0): Core framework.
* `laravel/breeze` (^2.0): Scaffolding auth.
* `livewire/livewire` (^3.0): Reaktivitas komponen real-time.
* `spatie/laravel-permission` (^6.0): Otorisasi berbasis peran (RBAC).
* `simplesoftwareio/simple-qrcode` (^4.2): Pembuat gambar QR Code.
* `maatwebsite/excel` (^3.1): Ekspor berkas Excel berformat SPJ resmi.
* `predis/predis` (^2.0): Driver koneksi Redis untuk cache dan session.

### 8.2 npm (JavaScript)
* `tailwindcss` (^3.4): Styling responsif mobile-first.
* `alpinejs` (^3.13): State interaksi UI ringan.
* `signature_pad` (^5.0): Kanvas sentuh tanda tangan digital.

---

## 9. Kesimpulan & Manfaat Migrasi

1. **Skalabilitas & Keandalan:** Mengeliminasi batas kuota eksekusi Google Apps Script (maksimal 6 menit per eksekusi dan kuota email/lock harian).
2. **Keamanan Bertaraf Enterprise:** Menggunakan hashing password standar industri (`bcrypt`), proteksi CSRF, sanitasi otomatis dari framework, dan rate limiting level web server.
3. **Penyempurnaan Data Presensi:** Tetap mempertahankan fitur unggulan dari kode lama seperti **Audit Trail**, **Koordinat GPS**, **Konfigurasi Isian Form Dinamis**, dan **Profil TTD Pimpinan**, namun dengan performa query MySQL yang jauh lebih cepat dan terstruktur.
