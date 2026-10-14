# Product Requirement Document (PRD)
## Sistem Daftar Hadir Online Berbasis QR Code & E-Signature

* **Nama Produk:** Sistem Daftar Hadir Online Berbasis QR Code BBKK Tanjung Priok
* **Target Institusi:** Balai Besar Kekarantinaan Kesehatan Tanjung Priok, Kementerian Kesehatan RI
* **Versi Dokumen:** 1.0.0
* **Status:** Siap Pengembangan (Approved for Development)
* **Target Rilis:** 2026

---

## 1. Ringkasan Eksekutif & Latar Belakang

### 1.1 Latar Belakang
Pencatatan presensi kegiatan kedinasan secara manual menggunakan lembar kertas fisik menimbulkan sejumlah kendala operasional: antrean panjang peserta di meja registrasi, tulisan tangan yang sulit dibaca, risiko kehilangan atau kerusakan berkas, serta proses rekapitulasi data yang lambat untuk kebutuhan administrasi dan Surat Pertanggungjawaban (SPJ) keuangan.

### 1.2 Tujuan Proyek
* Menerapkan digitalisasi administrasi rapat dan kegiatan kedinasan secara penuh (*paperless*).
* Menghadirkan mekanisme presensi mandiri yang cepat (di bawah 30 detik per peserta) melalui pemindaian QR Code di gawai masing-masing.
* Memastikan validitas dan akuntabilitas data melalui pembubuhan tanda tangan elektronik (*e-signature*) langsung pada aplikasi.
* Mengotomatisasi rekapitulasi daftar hadir dan survei Indeks Kepuasan Masyarakat (IKM) secara waktu nyata (*real-time*).

### 1.3 Metrik Keberhasilan (OKRs / KPIs)
* **Tingkat Adopsi:** Minimal 95% peserta rapat kedinasan melakukan presensi secara mandiri melalui sistem digital.
* **Efisiensi Waktu Rekapitulasi:** Penarikan berkas rekapitulasi kehadiran (format Excel siap cetak) selesai dalam waktu kurang dari 1 menit setelah sesi rapat ditutup.
* **Akurasi Data:** 0% duplikasi presensi (*zero duplicate submissions*) pada agenda yang sama.
* **Tingkat Kepuasan (IKM):** Minimal 90% peserta memberikan penilaian kategori "Baik" atau "Sangat Baik" terhadap kemudahan sistem.

---

## 2. Pengguna & Matriks Hak Akses (Role-Based Access Control)

| Peran (*Role*) | Deskripsi | Hak Akses Utama |
| :--- | :--- | :--- |
| **Super Admin** | Pengelola sistem terpusat dan tata kelola ruang rapat institusi. | • Memeriksa antrean permohonan kegiatan.<br>• Memberikan otorisasi persetujuan (*Approve*) atau penolakan (*Reject*).<br>• Memantau ketersediaan ruang rapat dan mencegah bentrok jadwal.<br>• Mengunduh rekapitulasi global dan dasbor IKM. |
| **Admin Tim Kerja** | Perwakilan tim kerja/bagian penyelenggara kegiatan. | • Mengajukan jadwal dan reservasi ruang rapat minimal H-1.<br>• Menayangkan QR Code di layar monitor/proyektor (*Display Mode*).<br>• Memantau kehadiran peserta tim kerja secara langsung.<br>• Mengunduh rekapitulasi daftar hadir per kegiatan. |
| **Peserta Rapat** | Seluruh aparatur internal BBKK maupun tamu eksternal. | • Memindai QR Code kegiatan menggunakan kamera gawai.<br>• Mengisi form identitas kedinasan.<br>• Membubuhkan tanda tangan elektronik pada kanvas digital.<br>• Mengisi survei evaluasi kepuasan kegiatan (IKM). |

---

## 3. Alur Pengguna (User Journey & System Flow)

```text
[ Admin Tim Kerja ]       [ Super Admin ]             [ Peserta Rapat ]
        │                         │                           │
  1. Input Pengajuan (H-1)        │                           │
        │────────────────────────>│                           │
        │                   2. Verifikasi                     │
        │                   & Cek Bentrok                     │
        │                         │                           │
        │<────────────────────────┤                           │
  3. Status "Disetujui"     (Disetujui / Ditolak)             │
  & QR Token Terbit               │                           │
        │                         │                           │
  4. Buka /display/[token]        │                           │
  (Tayang di Proyektor)           │                           │
        │                         │                           │
        │                         │                     5. Pindai QR Code
        │                         │                     melalui Kamera HP
        │                         │                           │
        │                         │                     6. Input Identitas &
        │                         │                        Nomor Rekening*
        │                         │                           │
        │                         │                     7. Tanda Tangan
        │                         │                        pada Kanvas Digital
        │                         │                           │
        │                         │                     8. Isi Survei IKM
        │                         │                           │
        │<────────────────────────┴───────────────────────────┤
        │            9. Data Masuk ke Basis Data Terpusat      │
        │          (Tersedia untuk Rekap SPJ & Laporan)       │
```

---

## 4. Kebutuhan Fungsional (Functional Requirements)

### 4.1 Modul Manajemen Kegiatan & Ruangan (Admin Tim Kerja & Super Admin)
* **FR-01: Validasi Batas Waktu Pengajuan (H-1)** `[Prioritas: P0]`
  * Sistem wajib menolak pembuatan agenda baru jika tanggal pelaksanaan adalah hari yang sama (H-0). Pengajuan hanya dapat diproses minimal 1 hari kalender sebelum pelaksanaan.
* **FR-02: Deteksi Bentrok Jadwal Ruangan (*Collision Detection*)** `[Prioritas: P0]`
  * Sistem wajib memvalidasi ketersediaan ruang rapat terpilih. Jika terdapat irisan waktu rapat pada ruangan yang sama di tanggal yang sama, sistem menampilkan peringatan bentrok dan mencegah persetujuan ganda.
* **FR-03: Penerbitan Token QR Dinamis** `[Prioritas: P0]`
  * Setelah kegiatan disetujui oleh Super Admin, sistem menerbitkan token alfanumerik unik (UUID/CUID) sebagai identifikasi rute tautan presensi.
* **FR-04: Penolakan Kegiatan Berbasis Catatan** `[Prioritas: P1]`
  * Jika Super Admin menolak pengajuan, kolom catatan alasan penolakan wajib diisi agar Admin Tim Kerja dapat melakukan revisi.

### 4.2 Modul Tampilan Proyektor (*Display Mode*)
* **FR-05: Antarmuka Monitor 16:9 Tanpa Navigasi** `[Prioritas: P0]`
  * Sistem menyediakan rute `/display/[token]` khusus proyektor yang menampilkan QR Code beresolusi tinggi, judul rapat, lokasi ruangan, jam pelaksanaan, dan panduan 3 langkah presensi ringkas.
* **FR-06: Penghitung Kehadiran Waktu Nyata (*Live Counter*)** `[Prioritas: P1]`
  * Menampilkan jumlah total peserta yang telah berhasil mengisi presensi secara *real-time*.

### 4.3 Modul Presensi Peserta (Mobile Web)
* **FR-07: Verifikasi Validitas Sesi Kegiatan** `[Prioritas: P0]`
  * Rute `/presensi/[token]` memeriksa status kegiatan. Jika kegiatan berstatus selain `APPROVED`, sistem menampilkan pesan bahwa sesi belum dibuka atau telah ditutup.
* **FR-08: Formulir Identitas Kedinasan** `[Prioritas: P0]`
  * Mengumpulkan data: Nama Lengkap beserta gelar, Tipe Instansi (Internal/Eksternal), Nama Bagian/Instansi Asal, NIP/ID Pegawai (validasi numerik 18 digit untuk PNS), Jenis Kelamin, dan Jabatan Kedinasan.
* **FR-09: Komponen Rekening Kondisional** `[Prioritas: P0]`
  * Bidang rekening bank disembunyikan secara default. Jika kotak centang *"Ada biaya penggantian transport"* dicentang, sistem menampilkan isian: Nama Bank, Nomor Rekening, dan Nama Pemilik Rekening.
* **FR-10: Pencegahan Duplikasi Data Identitas** `[Prioritas: P0]`
  * Sistem menolak pengiriman data jika kombinasi `eventId` dan `nipOrId` telah tercatat di dalam basis data.
* **FR-11: Modul Tanda Tangan Elektronik (*E-Signature Canvas*)** `[Prioritas: P0]`
  * Kanvas responsif berbasis sentuhan gawai dengan fitur hapus/ulang dan konversi ke format Base64 PNG. Kanvas wajib mengunci gulir layar (*scroll-lock*) saat jari bersentuhan dengan bidang tanda tangan.
* **FR-12: Evaluasi Indeks Kepuasan Masyarakat (IKM)** `[Prioritas: P0]`
  * Terdiri dari 4 skala pilihan terstandar: `SANGAT_BAIK`, `BAIK`, `CUKUP`, `KURANG`, serta satu area teks terbuka opsional untuk catatan/saran perbaikan.

### 4.4 Modul Laporan & Ekspor Data
* **FR-13: Ekspor Format SPJ Excel (.xlsx)** `[Prioritas: P0]`
  * Menghasilkan berkas Excel terstruktur mencakup kop dinas, tabel daftar nama peserta, NIP, jabatan, instansi, rincian rekening bank, stempel waktu, dan status verifikasi tanda tangan digital.

---

## 5. Kebutuhan Non-Fungsional (Non-Functional Requirements)

* **NFR-01: Aksesibilitas & Responsivitas (Mobile-First)**
  * Ukuran tombol dan area sentuh minimal $48 \times 48\text{ px}$.
  * Ukuran huruf dasar minimal $16\text{ px}$ untuk mencegah perbesaran otomatis (*auto-zoom*) pada peramban seluler (Safari/Chrome).
  * Mematuhi standar kontras warna WCAG 2.1 AA (menggunakan warna tema resmi hijau Kementerian Kesehatan dan teks Slate-900).
* **NFR-02: Kinerja & Latensi (*Performance*)**
  * Waktu muat halaman formulir presensi di bawah 2 detik pada koneksi seluler 4G standar.
  * Ukuran payload transmisi tanda tangan Base64 dioptimalkan agar tidak membebani jaringan (< 100 KB per pengiriman).
* **NFR-03: Keandalan & Integritas Data (*Integrity*)**
  * Terapkan *constraint* unik di tingkat basis data untuk menjamin integritas relasi tabel.
  * Dukungan transaksi basis data (*atomic transaction*) saat menyimpan data presensi dan evaluasi IKM secara bersamaan.
* **NFR-04: Kompatibilitas Peramban (*Cross-Browser*)**
  * Dapat diakses langsung tanpa instalasi aplikasi native pada peramban modern: Google Chrome, Apple Safari, Mozilla Firefox, dan Microsoft Edge.

---

## 6. Spesifikasi Skema Data (Data Model)

### 6.1 Entitas & Atribut

```text
User (Pengguna Sistem)
├── id (String, PK)
├── username (String, Unique)
├── passwordHash (String)
├── name (String)
├── role (Enum: SUPER_ADMIN, ADMIN_TIM_KERJA)
├── workUnit (String, Nullable)
└── createdAt (DateTime)

Room (Ruang Rapat)
├── id (String, PK)
├── name (String, Unique)
├── capacity (Int, Nullable)
└── isActive (Boolean)

Event (Agenda Kegiatan)
├── id (String, PK)
├── title (String)
├── roomId (String, FK -> Room.id)
├── eventDate (DateTime, Date Only)
├── startMinutes (Int, Menit dari 00:00)
├── endMinutes (Int, Menit dari 00:00)
├── workUnit (String)
├── status (Enum: PENDING, APPROVED, REJECTED, CLOSED)
├── rejectionNote (String, Nullable)
├── qrToken (String, Unique)
├── createdById (String, FK -> User.id)
├── createdAt (DateTime)
└── updatedAt (DateTime)

Attendee (Peserta Presensi)
├── id (String, PK)
├── eventId (String, FK -> Event.id)
├── fullName (String)
├── agency (String)
├── isInternal (Boolean)
├── gender (Enum: LAKI_LAKI, PEREMPUAN)
├── nipOrId (String)
├── position (String)
├── bankName (String, Nullable)
├── bankAccountNo (String, Nullable)
├── bankAccountName (String, Nullable)
├── signatureData (String, Base64 PNG)
├── createdAt (DateTime)
└── [Constraint: UNIQUE(eventId, nipOrId)]

Evaluation (IKM Kegiatan)
├── id (String, PK)
├── attendeeId (String, Unique, FK -> Attendee.id)
├── rating (Enum: SANGAT_BAIK, BAIK, CUKUP, KURANG)
├── feedback (String, Nullable)
└── createdAt (DateTime)
```

---

## 7. Penanganan Kasus Khusus (*Edge Cases*)

| Kasus | Dampak | Penanganan Sistem |
| :--- | :--- | :--- |
| **Peserta mengisi dua kali** | Duplikasi data pada laporan SPJ. | Sistem menolak pengiriman data kedua dan menampilkan pesan: *"NIP/ID ini telah terdaftar pada kegiatan ini."* |
| **Gawai dirotasi saat tanda tangan** | Kanvas bergeser atau coretan terpotong. | Menggunakan *ResizeObserver* dengan pencegahan *touch-action: none* untuk mempertahankan posisi goresan kanvas. |
| **Dua tim memesan ruangan dan jam yang sama** | Konflik jadwal ruang rapat fisik. | Algoritma *collision detection* otomatis memblokir pengajuan kedua dan memberi notifikasi konflik kepada Super Admin. |
| **Tanda tangan belum dibubuhkan** | Berkas tidak sah di mata bendahara. | Validasi form menolak submit dan memberi sorotan merah pada kotak kanvas dengan instruksi wajib isi. |
| **Kegiatan belum disetujui Super Admin** | Peserta mengakses tautan prematur. | Halaman menampilkan status informasi: *"Kegiatan ini sedang menunggu persetujuan verifikator resmi."* |

---

## 8. Rencana Tahapan Rilis (Milestones & Roadmap)

* **Fase 1: Inisialisasi Basis Data & Autentikasi (Minggu 1)**
  * Penyiapan skema Prisma, seeder akun awal, dan sistem login berbasis peran.
* **Fase 2: Portal Manajemen Acara & Tampilan QR (Minggu 2)**
  * Pembuatan antarmuka pengajuan Admin Tim Kerja, modul verifikasi Super Admin, dan halaman display proyektor 16:9.
* **Fase 3: Antarmuka Publik Peserta & E-Sign (Minggu 3)**
  * Pengembangan formulir presensi mobile-first, integrasi kanvas tanda tangan anti-offset, dan formulir IKM.
* **Fase 4: Modul Ekspor Excel & Pengujian Lapangan (Minggu 4)**
  * Integrasi engine ExcelJS untuk cetak SPJ, simulasi beban kehadiran serentak, dan uji coba operasional di lingkungan BBKK Tanjung Priok.