<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Events\ApproveEventAction;
use App\Enums\EventStatus;
use App\Enums\Gender;
use App\Enums\IkmRating;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Room;
use App\Models\User;
use App\Services\CollisionDetectionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class HadirInCoreTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $adminTimKerja;
    private Room $room;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::create([
            'username' => 'superadmin_test',
            'name' => 'Super Admin Test',
            'email' => 'super@test.com',
            'password' => 'secret123',
            'role' => UserRole::SUPER_ADMIN,
        ]);

        $this->adminTimKerja = User::create([
            'username' => 'timker_test',
            'name' => 'Tim Kerja Test',
            'email' => 'timker@test.com',
            'password' => 'secret123',
            'role' => UserRole::ADMIN_TIM_KERJA,
            'work_unit' => 'Seksi Surveilans',
        ]);

        $this->room = Room::create([
            'name' => 'Ruang Rapat Utama',
            'capacity' => 30,
            'is_active' => true,
        ]);
    }

    public function test_fr01_rejects_same_day_event_submission_and_accepts_h_minus_1(): void
    {
        $this->actingAs($this->adminTimKerja);

        // H-0 (Hari ini) wajib ditolak
        $responseH0 = $this->post(route('admin-tim-kerja.events.store'), [
            'title' => 'Rapat Dadakan Hari Ini',
            'room_id' => $this->room->id,
            'event_date' => Carbon::today()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '11:00',
            'work_unit' => 'Seksi Surveilans',
        ]);

        $responseH0->assertSessionHasErrors(['event_date']);

        // H-1 (Besok) wajib diterima
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        $responseH1 = $this->post(route('admin-tim-kerja.events.store'), [
            'title' => 'Rapat Resmi Terencana',
            'room_id' => $this->room->id,
            'event_date' => $tomorrow,
            'start_time' => '09:00',
            'end_time' => '11:00',
            'work_unit' => 'Seksi Surveilans',
        ]);

        $responseH1->assertRedirect(route('admin-tim-kerja.events.index'));
        $this->assertDatabaseHas('events', [
            'title' => 'Rapat Resmi Terencana',
            'status' => 'PENDING',
        ]);
    }

    public function test_fr02_collision_detection_prevents_overlapping_schedules(): void
    {
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');

        // Event pertama: 09:00 - 12:00 (540 - 720)
        Event::create([
            'title' => 'Acara Pertama',
            'room_id' => $this->room->id,
            'event_date' => $tomorrow,
            'start_minutes' => 540,
            'end_minutes' => 720,
            'work_unit' => 'Seksi A',
            'status' => EventStatus::APPROVED,
            'qr_token' => (string) \Illuminate\Support\Str::uuid(),
            'created_by' => $this->adminTimKerja->id,
        ]);

        $collisionService = app(CollisionDetectionService::class);

        // Irisan 10:00 - 13:00 (600 - 780) -> BENTROK
        $this->assertTrue(
            $collisionService->hasCollision(
                roomId: $this->room->id,
                eventDate: $tomorrow,
                startMinutes: 600,
                endMinutes: 780
            )
        );

        // Tidak beririsan: 13:00 - 15:00 (780 - 900) -> TIDAK BENTROK
        $this->assertFalse(
            $collisionService->hasCollision(
                roomId: $this->room->id,
                eventDate: $tomorrow,
                startMinutes: 780,
                endMinutes: 900
            )
        );
    }

    public function test_fr03_super_admin_approval_generates_qr_token(): void
    {
        $event = Event::create([
            'title' => 'Agenda Menunggu Approval',
            'room_id' => $this->room->id,
            'event_date' => Carbon::tomorrow()->format('Y-m-d'),
            'start_minutes' => 540,
            'end_minutes' => 720,
            'work_unit' => 'Seksi A',
            'status' => EventStatus::PENDING,
            'qr_token' => (string) \Illuminate\Support\Str::uuid(),
            'created_by' => $this->adminTimKerja->id,
        ]);

        $this->actingAs($this->superAdmin);

        $response = $this->post(route('super-admin.events.approve', $event));
        $response->assertRedirect();

        $event->refresh();
        $this->assertEquals(EventStatus::APPROVED, $event->status);
        $this->assertNotEmpty($event->qr_token);
    }

    public function test_fr08_attendee_presensi_submission_and_duplicate_prevention(): void
    {
        $event = Event::create([
            'title' => 'Rapat Pleno Koordinasi Pelabuhan',
            'room_id' => $this->room->id,
            'event_date' => Carbon::tomorrow()->format('Y-m-d'),
            'start_minutes' => 540,
            'end_minutes' => 720,
            'work_unit' => 'Seksi A',
            'status' => EventStatus::APPROVED,
            'qr_token' => (string) \Illuminate\Support\Str::uuid(),
            'created_by' => $this->adminTimKerja->id,
        ]);

        $payload = [
            'full_name' => 'dr. Ahmad Dahlan, Sp.KJ',
            'agency' => 'BBKK Tanjung Priok',
            'is_internal' => 1,
            'gender' => Gender::LAKI_LAKI->value,
            'nip_or_id' => '198801012015021001',
            'position' => 'Dokter Ahli Muda',
            'phone' => '081234567890',
            'address' => 'Jl. Pelabuhan Raya No. 1, Jakarta Utara',
            'signature_data' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ];

        // 1. Submit pertama berhasil
        $response = $this->post(route('presensi.store', ['token' => $event->qr_token]), $payload);
        $response->assertRedirect();

        $this->assertDatabaseHas('attendees', [
            'event_id' => $event->id,
            'nip_or_id' => '198801012015021001',
        ]);

        // 2. Submit kedua dengan NIP yang sama pada kegiatan yang sama DITOLAK (Zero Duplicates)
        $responseDuplicate = $this->post(route('presensi.store', ['token' => $event->qr_token]), $payload);
        $responseDuplicate->assertSessionHasErrors(['nip_or_id']);
        $this->assertEquals(1, $event->attendees()->count());
    }

    public function test_fr10_ikm_evaluation_submission(): void
    {
        $event = Event::create([
            'title' => 'Rapat Evaluasi IKM',
            'room_id' => $this->room->id,
            'event_date' => Carbon::tomorrow()->format('Y-m-d'),
            'start_minutes' => 540,
            'end_minutes' => 720,
            'work_unit' => 'Seksi A',
            'status' => EventStatus::APPROVED,
            'qr_token' => (string) \Illuminate\Support\Str::uuid(),
            'created_by' => $this->adminTimKerja->id,
        ]);

        $attendee = $event->attendees()->create([
            'full_name' => 'Siti Aminah, S.Kep',
            'agency' => 'Puskesmas Koja',
            'is_internal' => 0,
            'gender' => Gender::PEREMPUAN,
            'nip_or_id' => '199203152019032005',
            'position' => 'Perawat Ahli Pertama',
            'phone' => '08987654321',
            'address' => 'Jakarta Utara',
            'signature_data' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);

        $response = $this->post(route('presensi.survey.store', [
            'token' => $event->qr_token,
            'attendee' => $attendee->id,
        ]), [
            'rating' => IkmRating::SANGAT_BAIK->value,
            'feedback' => 'Sistem presensi online sangat cepat dan efisien.',
        ]);

        $response->assertRedirect(route('presensi.success', [
            'token' => $event->qr_token,
            'attendee' => $attendee->id,
        ]));

        $this->assertDatabaseHas('evaluations', [
            'attendee_id' => $attendee->id,
            'rating' => IkmRating::SANGAT_BAIK->value,
        ]);
    }

    public function test_dynamic_config_fields_in_event_and_attendee_submission(): void
    {
        $room = Room::create([
            'name' => 'Ruang Akustik',
            'capacity' => 20,
            'is_active' => true,
        ]);

        $customFields = [
            'full_name' => ['enabled' => true, 'required' => true],
            'nip_or_id' => ['enabled' => false, 'required' => false],
            'gender' => ['enabled' => false, 'required' => false],
            'agency' => ['enabled' => true, 'required' => false],
            'position' => ['enabled' => false, 'required' => false],
            'phone' => ['enabled' => true, 'required' => false],
            'address' => ['enabled' => false, 'required' => false],
            'coordinates' => ['enabled' => false, 'required' => false],
            'transport' => ['enabled' => false, 'required' => false],
        ];

        $event = Event::create([
            'title' => 'Rapat Koordinasi Singkat',
            'room_id' => $room->id,
            'event_date' => Carbon::tomorrow()->format('Y-m-d'),
            'start_minutes' => 600,
            'end_minutes' => 700,
            'work_unit' => 'Tim Kerja A',
            'status' => EventStatus::APPROVED,
            'qr_token' => (string) \Illuminate\Support\Str::uuid(),
            'config_fields' => $customFields,
            'created_by' => $this->adminTimKerja->id,
        ]);

        // Access presensi form
        $formResponse = $this->get(route('presensi.show', ['token' => $event->qr_token]));
        $formResponse->assertStatus(200);
        $formResponse->assertSee('Nama Lengkap & Gelar', false);
        $formResponse->assertSee('Instansi / Unit Asal');
        $formResponse->assertSee('Nomor HP / WhatsApp');
        // Unchecked fields must NOT appear
        $formResponse->assertDontSee('NIP / NIK Pegawai');
        $formResponse->assertDontSee('Jenis Kelamin');
        $formResponse->assertDontSee('Jabatan Kedinasan');
        $formResponse->assertDontSee('Alamat Asal / Kantor');

        // Submit attendance without NIP, gender, position, address, transport
        $submitResponse = $this->post(route('presensi.store', ['token' => $event->qr_token]), [
            'is_internal' => 1,
            'full_name' => 'Dr. H. Ahmad Fauzi',
            'agency' => 'Kemenkes Pusat',
            'phone' => '081234567890',
            'signature_data' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);

        $submitResponse->assertSessionHasNoErrors();
        $this->assertDatabaseHas('attendees', [
            'event_id' => $event->id,
            'full_name' => 'Dr. H. Ahmad Fauzi',
            'nip_or_id' => null,
        ]);
    }

    public function test_super_admin_can_view_all_tim_kerja_events_in_admin_tim_kerja_module(): void
    {
        $room = Room::create([
            'name' => 'Ruang Samudera 2',
            'capacity' => 15,
            'is_active' => true,
        ]);

        $otherAdmin = User::create([
            'name' => 'Admin Tim Kerja Lain',
            'username' => 'admin_lain',
            'password' => bcrypt('password123'),
            'role' => \App\Enums\UserRole::ADMIN_TIM_KERJA,
            'work_unit' => 'Tim Kerja Pengendalian Karantina',
        ]);

        $event1 = Event::create([
            'title' => 'Agenda Dari Tim A',
            'room_id' => $room->id,
            'event_date' => Carbon::tomorrow()->format('Y-m-d'),
            'start_minutes' => 540,
            'end_minutes' => 600,
            'work_unit' => 'Tim Kerja A',
            'status' => EventStatus::APPROVED,
            'qr_token' => (string) \Illuminate\Support\Str::uuid(),
            'created_by' => $this->adminTimKerja->id,
        ]);

        $event2 = Event::create([
            'title' => 'Agenda Dari Tim Lain',
            'room_id' => $room->id,
            'event_date' => Carbon::tomorrow()->format('Y-m-d'),
            'start_minutes' => 660,
            'end_minutes' => 720,
            'work_unit' => 'Tim Kerja Pengendalian Karantina',
            'status' => EventStatus::APPROVED,
            'qr_token' => (string) \Illuminate\Support\Str::uuid(),
            'created_by' => $otherAdmin->id,
        ]);

        // Regular admin tim kerja only sees their own
        $responseRegular = $this->actingAs($this->adminTimKerja)
            ->get(route('admin-tim-kerja.events.index'));
        $responseRegular->assertStatus(200);
        $responseRegular->assertSee('Agenda Dari Tim A');
        $responseRegular->assertDontSee('Agenda Dari Tim Lain');

        // Super Admin sees ALL events from all tim kerja
        $responseSuper = $this->actingAs($this->superAdmin)
            ->get(route('admin-tim-kerja.events.index'));
        $responseSuper->assertStatus(200);
        $responseSuper->assertSee('Agenda Dari Tim A');
        $responseSuper->assertSee('Agenda Dari Tim Lain');
        $responseSuper->assertSee('Semua Tim Kerja');
    }

    public function test_presensi_form_has_no_status_asal_peserta_and_submission_succeeds(): void
    {
        $event = Event::create([
            'title' => 'Rapat Koordinasi Bebas Status Asal',
            'room_id' => $this->room->id,
            'event_date' => Carbon::tomorrow()->format('Y-m-d'),
            'start_minutes' => 540,
            'end_minutes' => 720,
            'work_unit' => 'Seksi A',
            'status' => EventStatus::APPROVED,
            'qr_token' => (string) \Illuminate\Support\Str::uuid(),
            'created_by' => $this->adminTimKerja->id,
        ]);

        // Form check: no Status Asal Peserta selection buttons
        $response = $this->get(route('presensi.show', ['token' => $event->qr_token]));
        $response->assertStatus(200);
        $response->assertDontSee('Status Asal Peserta');
        $response->assertDontSee('Tamu Eksternal');
        $response->assertDontSee('Internal BBKK Priok');

        // Submission without is_internal field succeeds
        $payload = [
            'full_name' => 'Budi Wicaksono, S.T.',
            'agency' => 'Dinas Kesehatan',
            'gender' => Gender::LAKI_LAKI->value,
            'nip_or_id' => '198505052010011005',
            'position' => 'Sanitarian Ahli Muda',
            'phone' => '081234567899',
            'address' => 'Jakarta Utara',
            'signature_data' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ];

        $submitResponse = $this->post(route('presensi.store', ['token' => $event->qr_token]), $payload);
        $submitResponse->assertRedirect();

        $this->assertDatabaseHas('attendees', [
            'event_id' => $event->id,
            'full_name' => 'Budi Wicaksono, S.T.',
            'nip_or_id' => '198505052010011005',
        ]);
    }

    public function test_login_flow_with_credentials_and_alias(): void
    {
        // 1. Login superadmin sukses
        $responseSuper = $this->post(route('login.attempt'), [
            'username' => 'superadmin_test',
            'password' => 'secret123',
        ]);
        $responseSuper->assertRedirect(route('super-admin.dashboard'));
        $this->assertAuthenticatedAs($this->superAdmin);

        // Logout
        $this->post(route('logout'));
        $this->assertGuest();

        // 2. Login admin tim kerja sukses
        $responseTimker = $this->post(route('login.attempt'), [
            'username' => 'timker_test',
            'password' => 'secret123',
        ]);
        $responseTimker->assertRedirect(route('admin-tim-kerja.events.index'));
        $this->assertAuthenticatedAs($this->adminTimKerja);

        // Logout
        $this->post(route('logout'));
        $this->assertGuest();

        // 3. Login dengan password salah gagal
        $responseFailed = $this->post(route('login.attempt'), [
            'username' => 'superadmin_test',
            'password' => 'PasswordYangSalahTotal999',
        ]);
        $responseFailed->assertSessionHasErrors(['username']);
        $this->assertGuest();
    }

    public function test_spj_attendance_export_omits_unfilled_columns_and_has_updated_headers(): void
    {
        $event = Event::create([
            'title' => 'Rapat Malam Koordinasi',
            'room_id' => $this->room->id,
            'event_date' => Carbon::tomorrow()->format('Y-m-d'),
            'start_minutes' => 540,
            'end_minutes' => 720,
            'work_unit' => 'Seksi A',
            'status' => EventStatus::APPROVED,
            'qr_token' => (string) \Illuminate\Support\Str::uuid(),
            'created_by' => $this->adminTimKerja->id,
        ]);

        // Attendee only has full_name, agency, nip_or_id filled
        $event->attendees()->create([
            'full_name' => 'Agi',
            'agency' => 'BBKK Tanjung Priok',
            'nip_or_id' => '199308222015031001',
            'signature_data' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);

        $export = new \App\Exports\SpjAttendanceExport($event);

        $headings = $export->headings();

        // Must include filled fields
        $this->assertContains('NO', $headings);
        $this->assertContains('NAMA LENGKAP & GELAR', $headings);
        $this->assertContains('INSTANSI / BAGIAN', $headings);
        $this->assertContains('NIP / NIK', $headings);
        $this->assertContains('TANDA TANGAN', $headings);
        $this->assertContains('WAKTU PRESENSI (WIB)', $headings);

        // Must NOT include unfilled fields
        $this->assertNotContains('JABATAN KEDINASAN', $headings);
        $this->assertNotContains('JENIS KELAMIN', $headings);
        $this->assertNotContains('NO HP / WHATSAPP', $headings);
        $this->assertNotContains('ALAMAT ASAL', $headings);
        $this->assertNotContains('BANK', $headings);
        $this->assertNotContains('NO. REKENING', $headings);
        $this->assertNotContains('ATAS NAMA REKENING', $headings);

        // Verify collection row only has active keys
        $collection = $export->collection();
        $this->assertCount(1, $collection);
        $firstRow = $collection->first();
        $this->assertEquals('Agi', $firstRow['full_name']);
        $this->assertEquals('BBKK Tanjung Priok', $firstRow['agency']);
        $this->assertArrayNotHasKey('position', $firstRow);
        $this->assertArrayNotHasKey('gender', $firstRow);

        // Verify styles & worksheet header texts & drawings
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $export->styles($sheet);

        $this->assertEquals('BALAI BESAR KEKARANTINAAN KESEHATAN TANJUNG PRIOK', $sheet->getCell('A1')->getValue());
        $this->assertEquals('REKAPITULASI DAFTAR HADIR PERTEMUAN', $sheet->getCell('A2')->getValue());
        $this->assertStringStartsWith('Kegiatan: Rapat Malam Koordinasi', (string) $sheet->getCell('A3')->getValue());

        $drawings = $sheet->getDrawingCollection();
        $this->assertNotEmpty($drawings);
        $this->assertStringContainsString('TTD Agi', $drawings[0]->getName());

        // Verify route export download works (Excel)
        $downloadResponse = $this->actingAs($this->adminTimKerja)
            ->get(route('admin-tim-kerja.events.export', ['event' => $event->id]));
        $downloadResponse->assertOk();

        // Verify route export download works (PDF)
        $pdfResponse = $this->actingAs($this->adminTimKerja)
            ->get(route('admin-tim-kerja.events.export-pdf', ['event' => $event->id]));
        $pdfResponse->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $pdfResponse->headers->get('content-type'));

        $superPdfResponse = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.events.export-pdf', ['event' => $event->id]));
        $superPdfResponse->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $superPdfResponse->headers->get('content-type'));

        $otherUser = User::create([
            'username' => 'other_user',
            'name' => 'Other User',
            'email' => 'other@test.com',
            'password' => 'secret123',
            'role' => UserRole::ADMIN_TIM_KERJA,
            'work_unit' => 'Seksi Lain',
        ]);
        $unauthorizedPdf = $this->actingAs($otherUser)
            ->get(route('admin-tim-kerja.events.export-pdf', ['event' => $event->id]));
        $unauthorizedPdf->assertForbidden();

        // 2. Test event with Gender enum does NOT throw 'Object of class App\Enums\Gender could not be converted to string'
        $eventWithGender = Event::create([
            'title' => 'Rapat Gender Test',
            'room_id' => $this->room->id,
            'event_date' => Carbon::tomorrow()->format('Y-m-d'),
            'start_minutes' => 540,
            'end_minutes' => 720,
            'work_unit' => 'Seksi B',
            'status' => EventStatus::APPROVED,
            'qr_token' => (string) \Illuminate\Support\Str::uuid(),
            'created_by' => $this->adminTimKerja->id,
        ]);

        $eventWithGender->attendees()->create([
            'full_name' => 'Siti Nurhaliza',
            'gender' => Gender::PEREMPUAN,
            'signature_data' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);

        $exportGender = new \App\Exports\SpjAttendanceExport($eventWithGender);
        $genderHeadings = $exportGender->headings();
        $this->assertContains('JENIS KELAMIN', $genderHeadings);

        $genderCollection = $exportGender->collection();
        $this->assertEquals('Perempuan', $genderCollection->first()['gender']);

        $genderDownload = $this->actingAs($this->adminTimKerja)
            ->get(route('admin-tim-kerja.events.export', ['event' => $eventWithGender->id]));
        $genderDownload->assertOk();
    }

    public function test_super_admin_can_toggle_event_status_via_ajax_and_form(): void
    {
        $event = Event::create([
            'title' => 'Rapat Koordinasi Status Toggle',
            'room_id' => $this->room->id,
            'event_date' => Carbon::tomorrow()->format('Y-m-d'),
            'start_minutes' => 540,
            'end_minutes' => 720,
            'work_unit' => 'Seksi Surveilans',
            'status' => EventStatus::APPROVED,
            'qr_token' => (string) \Illuminate\Support\Str::uuid(),
            'created_by' => $this->adminTimKerja->id,
        ]);

        // 0. Verify page renders toggle component
        $pageResponse = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.events.index'));
        $pageResponse->assertOk()
            ->assertSee('eventStatusToggle')
            ->assertSee('toggle-form-' . $event->id);

        // 1. Super Admin toggles APPROVED -> CLOSED via AJAX
        $ajaxResponse = $this->actingAs($this->superAdmin)
            ->postJson(route('super-admin.events.toggle-status', $event));

        $ajaxResponse->assertOk()
            ->assertJson([
                'success' => true,
                'isOpen' => false,
                'status' => 'CLOSED',
                'label' => 'Tutup',
                'badgeLabel' => 'Selesai / Ditutup',
            ]);

        $this->assertEquals(EventStatus::CLOSED, $event->fresh()->status);

        // 2. Super Admin toggles CLOSED -> APPROVED via Form POST
        $formResponse = $this->actingAs($this->superAdmin)
            ->post(route('super-admin.events.toggle-status', $event));

        $formResponse->assertRedirect();
        $this->assertEquals(EventStatus::APPROVED, $event->fresh()->status);

        // 3. Admin Tim Kerja is forbidden from calling super-admin toggle
        $forbiddenResponse = $this->actingAs($this->adminTimKerja)
            ->post(route('super-admin.events.toggle-status', $event));
        $forbiddenResponse->assertForbidden();

        // 4. Cannot toggle a PENDING event
        $pendingEvent = Event::create([
            'title' => 'Rapat Pending Status',
            'room_id' => $this->room->id,
            'event_date' => Carbon::tomorrow()->format('Y-m-d'),
            'start_minutes' => 540,
            'end_minutes' => 720,
            'work_unit' => 'Seksi Surveilans',
            'status' => EventStatus::PENDING,
            'qr_token' => (string) \Illuminate\Support\Str::uuid(),
            'created_by' => $this->adminTimKerja->id,
        ]);

        $pendingResponse = $this->actingAs($this->superAdmin)
            ->postJson(route('super-admin.events.toggle-status', $pendingEvent));
        $pendingResponse->assertStatus(422);
    }

    public function test_admin_can_set_pimpinan_profile_and_signature_for_event(): void
    {
        $event = Event::create([
            'title' => 'Rapat Pengesahan Pimpinan',
            'room_id' => $this->room->id,
            'event_date' => Carbon::tomorrow()->format('Y-m-d'),
            'start_minutes' => 540,
            'end_minutes' => 720,
            'work_unit' => 'Seksi Surveilans',
            'status' => EventStatus::APPROVED,
            'qr_token' => (string) \Illuminate\Support\Str::uuid(),
            'created_by' => $this->adminTimKerja->id,
        ]);

        $payload = [
            'pimpinan_name' => 'Dra. ATIK YULIHARTI, M.Kes',
            'pimpinan_nip' => '196807201991032001',
            'pimpinan_position' => 'Ketua Tim Kerja Pelayanan Publik dan Pembangunan Zona Integritas',
            'pimpinan_date_text' => 'Jakarta, 14 Oktober 2026',
            'pimpinan_signature' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ];

        $response = $this->actingAs($this->adminTimKerja)
            ->post(route('admin-tim-kerja.events.pimpinan', $event), $payload);

        $response->assertRedirect();
        
        $fresh = $event->fresh();
        $this->assertEquals('Dra. ATIK YULIHARTI, M.Kes', $fresh->pimpinan_name);
        $this->assertEquals('196807201991032001', $fresh->pimpinan_nip);
        $this->assertEquals('Ketua Tim Kerja Pelayanan Publik dan Pembangunan Zona Integritas', $fresh->pimpinan_position);
        $this->assertEquals('Jakarta, 14 Oktober 2026', $fresh->pimpinan_date_text);
        $this->assertNotNull($fresh->pimpinan_signature);

        // Test export contains pimpinan information and signature drawing
        $export = new \App\Exports\SpjAttendanceExport($fresh);
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([$export->headings()], null, 'A6');
        $sheet->fromArray($export->collection()->toArray(), null, 'A7');
        $export->styles($sheet);

        $this->assertGreaterThanOrEqual(1, count($sheet->getDrawingCollection()));
        
        // Check cell values in the sheet
        $allCellValues = [];
        foreach ($sheet->getRowIterator() as $row) {
            foreach ($row->getCellIterator() as $cell) {
                if ($cell->getValue() !== null) {
                    $allCellValues[] = (string) $cell->getValue();
                }
            }
        }
        $combinedText = implode(' ', $allCellValues);
        $this->assertStringContainsString('Dra. ATIK YULIHARTI, M.Kes', $combinedText);
        $this->assertStringContainsString('196807201991032001', $combinedText);
        $this->assertStringContainsString('Ketua Tim Kerja Pelayanan Publik dan Pembangunan Zona Integritas', $combinedText);
        $this->assertStringContainsString('Jakarta, 14 Oktober 2026', $combinedText);

        // Test HTTP download response is OK
        $downloadResponse = $this->actingAs($this->adminTimKerja)
            ->get(route('admin-tim-kerja.events.export', $event));
        $downloadResponse->assertOk();
    }
}

