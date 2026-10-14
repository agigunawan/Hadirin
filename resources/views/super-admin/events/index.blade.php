@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ 
    rejectModal: false, 
    activeEventId: null, 
    eventTitle: '',
    pimpinanModalOpen: false,
    pimpinanEventId: null,
    pimpinanEventTitle: '',
    pimpinanName: '',
    pimpinanNip: '',
    pimpinanPosition: '',
    pimpinanDateText: '',
    existingSignature: '',
    isCanvasClean: true,
    pad: null,

    openPimpinanModal(data) {
        this.pimpinanEventId = data.id;
        this.pimpinanEventTitle = data.title;
        this.pimpinanName = data.name || '';
        this.pimpinanNip = data.nip || '';
        this.pimpinanPosition = data.position || '';
        this.pimpinanDateText = data.date_text || '';
        this.existingSignature = data.signature || '';
        this.isCanvasClean = true;
        this.pimpinanModalOpen = true;

        this.$nextTick(() => {
            const canvas = this.$refs.superAdminPimpinanCanvas;
            if (canvas && window.initSignaturePad) {
                if (!this.pad) {
                    this.pad = window.initSignaturePad(canvas);
                    this.pad.addEventListener('beginStroke', () => {
                        this.isCanvasClean = false;
                    });
                } else {
                    this.pad.resize();
                    this.pad.clear();
                }
            }
        });
    },

    clearPad() {
        if (this.pad) this.pad.clear();
        this.existingSignature = '';
        this.isCanvasClean = false;
    },

    submitPimpinanForm(e) {
        const hasDrawn = this.pad && !this.pad.isEmpty();
        if (hasDrawn) {
            this.$refs.superAdminSignatureInput.value = this.pad.toDataURL('image/png');
        } else if (this.existingSignature) {
            this.$refs.superAdminSignatureInput.value = this.existingSignature;
        } else {
            e.preventDefault();
            alert('Mohon goreskan tanda tangan pimpinan pada kotak yang disediakan.');
            return false;
        }
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Verifikasi & Tata Kelola Agenda</h1>
            <p class="text-xs text-slate-500 mt-1">Kelola persetujuan agenda rapat, ruang, penerbitan QR Code, dan unduhan berkas SPJ.</p>
        </div>

        <!-- Filter Tabs -->
        <div class="flex items-center gap-1.5 p-1 bg-slate-200/60 rounded-2xl text-xs font-bold text-slate-600">
            <a href="{{ route('super-admin.events.index') }}" class="px-3 py-1.5 rounded-xl transition-all {{ empty($currentFilter) ? 'bg-white text-slate-900 shadow-xs' : 'hover:text-slate-900' }}">Semua</a>
            <a href="{{ route('super-admin.events.index', ['status' => 'PENDING']) }}" class="px-3 py-1.5 rounded-xl transition-all {{ $currentFilter === 'PENDING' ? 'bg-white text-amber-700 shadow-xs' : 'hover:text-slate-900' }}">Menunggu</a>
            <a href="{{ route('super-admin.events.index', ['status' => 'APPROVED']) }}" class="px-3 py-1.5 rounded-xl transition-all {{ $currentFilter === 'APPROVED' ? 'bg-white text-emerald-700 shadow-xs' : 'hover:text-slate-900' }}">Disetujui</a>
            <a href="{{ route('super-admin.events.index', ['status' => 'CLOSED']) }}" class="px-3 py-1.5 rounded-xl transition-all {{ $currentFilter === 'CLOSED' ? 'bg-white text-slate-900 shadow-xs' : 'hover:text-slate-900' }}">Selesai</a>
        </div>
    </div>

    <!-- Events List Card -->
    <div class="bg-white rounded-2xl md:rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left text-xs min-w-[860px]">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-400 uppercase tracking-wider text-[10px]">
                        <th class="py-3 px-4 min-w-[200px]">Nama Kegiatan</th>
                        <th class="py-3 px-3 min-w-[130px]">Ruangan & Jam</th>
                        <th class="py-3 px-3 min-w-[130px]">Penyelenggara</th>
                        <th class="py-3 px-3 min-w-[120px]">Status & Presensi</th>
                        <th class="py-3 px-3 text-center min-w-[90px]">Tautan QR</th>
                        <th class="py-3 px-4 text-right min-w-[180px]">Aksi Manajemen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($events as $event)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-4 px-4">
                                <div class="font-bold text-slate-900 max-w-sm">{{ $event->title }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ $event->event_date->translatedFormat('l, d F Y') }}</div>
                                @if($event->rejection_note)
                                    <div class="mt-1 p-2 rounded-lg bg-rose-50 text-rose-700 text-[10px] border border-rose-200 max-w-xs">
                                        <span class="font-bold">Alasan Ditolak:</span> {{ $event->rejection_note }}
                                    </div>
                                @endif
                            </td>

                            <td class="py-4 px-4">
                                <div class="font-semibold text-slate-800">{{ $event->room->name }}</div>
                                <div class="text-[11px] text-slate-500 font-mono mt-0.5">{{ $event->formatted_time }}</div>
                            </td>

                            <td class="py-4 px-4">
                                <div class="font-medium text-slate-800">{{ $event->work_unit }}</div>
                                <div class="text-[11px] text-slate-400">Oleh: {{ $event->creator->name }}</div>
                            </td>

                            <td class="py-4 px-4"
                                @if(in_array($event->status->value, ['APPROVED', 'CLOSED']))
                                x-data="eventStatusToggle({
                                    eventId: '{{ $event->id }}',
                                    initialStatus: '{{ $event->status->value }}',
                                    initialBadgeClass: '{{ $event->status->badgeClass() }}',
                                    initialBadgeLabel: '{{ $event->status->label() }}',
                                    toggleUrl: '{{ route('super-admin.events.toggle-status', $event) }}'
                                })"
                                @endif
                            >
                                <div class="space-y-1.5">
                                    <span 
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border transition-colors duration-200 {{ $event->status->badgeClass() }}"
                                        @if(in_array($event->status->value, ['APPROVED', 'CLOSED']))
                                        :class="badgeClass"
                                        x-text="badgeLabel"
                                        @endif
                                    >
                                        {{ $event->status->label() }}
                                    </span>
                                    <div class="text-[11px] text-slate-500 font-medium">
                                        {{ $event->attendees_count }} Peserta Hadir
                                    </div>
                                    @if(in_array($event->status->value, ['APPROVED', 'CLOSED']))
                                        <div class="pt-1">
                                            <!-- Fallback Form for non-JS / emergency direct submit -->
                                            <form 
                                                id="toggle-form-{{ $event->id }}" 
                                                method="POST" 
                                                action="{{ route('super-admin.events.toggle-status', $event) }}" 
                                                class="hidden">
                                                @csrf
                                            </form>

                                            <div 
                                                class="inline-flex items-center gap-2 cursor-pointer select-none group py-0.5"
                                                @click="toggle()"
                                                onclick="if(!window.Alpine) { document.getElementById('toggle-form-{{ $event->id }}').submit(); }"
                                                :title="isOpen ? 'Klik untuk menutup presensi' : 'Klik untuk membuka presensi'"
                                                title="{{ $event->status === \App\Enums\EventStatus::APPROVED ? 'Klik untuk menutup presensi' : 'Klik untuk membuka presensi' }}"
                                                role="button"
                                                tabindex="0"
                                                @keydown.space.prevent="toggle()"
                                                @keydown.enter.prevent="toggle()">
                                                
                                                <button
                                                    type="button"
                                                    :disabled="isLoading"
                                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 {{ $event->status === \App\Enums\EventStatus::APPROVED ? 'bg-emerald-600' : 'bg-slate-300' }}"
                                                    :class="isOpen ? 'bg-emerald-600' : 'bg-slate-300'"
                                                    role="switch"
                                                    :aria-checked="isOpen ? 'true' : 'false'">
                                                    <span
                                                        aria-hidden="true"
                                                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $event->status === \App\Enums\EventStatus::APPROVED ? 'translate-x-5' : 'translate-x-0' }}"
                                                        :class="isOpen ? 'translate-x-5' : 'translate-x-0'"></span>
                                                </button>

                                                <span 
                                                    class="text-xs font-semibold transition-colors duration-200 flex items-center gap-1.5 {{ $event->status === \App\Enums\EventStatus::APPROVED ? 'text-emerald-700' : 'text-slate-500' }}"
                                                    :class="isOpen ? 'text-emerald-700' : 'text-slate-500'">
                                                    <span x-text="isOpen ? 'Buka' : 'Tutup'">{{ $event->status === \App\Enums\EventStatus::APPROVED ? 'Buka' : 'Tutup' }}</span>
                                                    <template x-if="isLoading">
                                                        <svg class="animate-spin h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                                        </svg>
                                                    </template>
                                                </span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <td class="py-4 px-4 text-center">
                                @if($event->status->value === 'APPROVED' || $event->status->value === 'CLOSED')
                                    <a href="{{ route('display.show', ['token' => $event->qr_token]) }}" 
                                       target="_blank" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 text-white hover:bg-slate-800 text-[11px] font-bold shadow-2xs transition-colors">
                                        <svg class="w-3.5 h-3.5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                        <span>Layar Proyektor</span>
                                    </a>
                                @else
                                    <span class="text-[11px] text-slate-400 font-medium">—</span>
                                @endif
                            </td>

                            <td class="py-4 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($event->status->value === 'PENDING')
                                        <!-- Approve Button -->
                                        <form method="POST" action="{{ route('super-admin.events.approve', $event) }}">
                                            @csrf
                                            <button type="submit" 
                                                    onclick="return confirm('Setujui agenda ini dan terbitkan token presensi?')"
                                                    class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] shadow-sm transition-colors">
                                                Setujui
                                            </button>
                                        </form>

                                        <!-- Reject Trigger -->
                                        <button type="button" 
                                                @click="rejectModal = true; activeEventId = '{{ $event->id }}'; eventTitle = '{{ addslashes($event->title) }}'"
                                                class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-[11px] border border-rose-200 transition-colors">
                                            Tolak
                                        </button>
                                    @endif

                                    <!-- SPJ Download Button (Excel) -->
                                    <a href="{{ route('super-admin.events.export', $event) }}" 
                                       title="Unduh Berkas Rekapitulasi SPJ Excel"
                                       class="p-2 rounded-xl border border-slate-200 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-700 text-slate-600 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </a>

                                    <!-- SPJ Download Button (PDF) -->
                                    <a href="{{ route('super-admin.events.export-pdf', $event) }}" 
                                       title="Unduh Berkas Rekapitulasi SPJ PDF"
                                       class="p-2 rounded-xl border border-slate-200 hover:bg-rose-50 hover:border-rose-300 hover:text-rose-700 text-slate-600 transition-colors">
                                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-6 4h4"/></svg>
                                    </a>

                                    @if(in_array($event->status->value, ['APPROVED', 'CLOSED']))
                                        <!-- Profil & Tanda Tangan Pimpinan Pengesahan (Gambar 1 & Gambar 2) -->
                                        <button type="button" 
                                                @click="openPimpinanModal({
                                                    id: '{{ $event->id }}',
                                                    title: '{{ addslashes($event->title) }}',
                                                    name: '{{ addslashes($event->pimpinan_name ?? '') }}',
                                                    nip: '{{ addslashes($event->pimpinan_nip ?? '') }}',
                                                    position: '{{ addslashes($event->pimpinan_position ?? ('Ketua ' . $event->work_unit)) }}',
                                                    date_text: '{{ addslashes($event->pimpinan_date_text ?? ('Jakarta, ' . $event->event_date->translatedFormat('d F Y'))) }}',
                                                    signature: '{{ $event->pimpinan_signature ? addslashes($event->pimpinan_signature) : '' }}'
                                                })"
                                                title="Profil & Tanda Tangan Pimpinan Pengesahan"
                                                class="p-2 rounded-xl border {{ $event->pimpinan_signature ? 'border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'border-slate-200 hover:bg-slate-100 text-slate-600' }} transition-colors flex items-center justify-center relative group">
                                            <svg class="w-4 h-4 {{ $event->pimpinan_signature ? 'text-emerald-700' : 'text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                            @if($event->pimpinan_signature)
                                                <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-emerald-500 rounded-full border-2 border-white shadow-xs" title="Tanda tangan pengesahan sudah terekam"></span>
                                            @endif
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400 text-xs">
                                Tidak ada agenda kegiatan yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $events->links() }}
        </div>
    </div>

    <!-- Reject Modal -->
    <div x-show="rejectModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         style="display: none;">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200" @click.away="rejectModal = false">
            <h3 class="text-base font-extrabold text-slate-900">Tolak Pengajuan Agenda</h3>
            <p class="text-xs text-slate-500 mt-1">Kegiatan: <span class="font-bold text-slate-700" x-text="eventTitle"></span></p>

            <form :action="'/super-admin/events/' + activeEventId + '/reject'" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1.5">Alasan Penolakan <span class="text-rose-500">*</span></label>
                    <textarea name="rejection_note" 
                              required 
                              rows="3" 
                              placeholder="Jelaskan alasan penolakan (misal: Ruangan bentrok dengan agenda darurat atau jadwal perlu digeser)..." 
                              class="form-input text-xs rounded-xl py-2.5"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="rejectModal = false" class="btn-secondary py-2 px-4 text-xs font-bold rounded-xl">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl transition-colors">Tolak Agenda</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Profil & Tanda Tangan Pimpinan -->
    <div x-show="pimpinanModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         style="display: none;">
        <div class="bg-white rounded-3xl max-w-2xl w-full p-6 md:p-7 shadow-2xl border border-slate-200 overflow-y-auto max-h-[92vh]" 
             @click.away="pimpinanModalOpen = false">
            
            <!-- Modal Header -->
            <div class="flex items-start justify-between gap-4 pb-4 mb-4 border-b border-slate-100">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-[10px] font-bold uppercase tracking-wider mb-1.5">
                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>Pengesahan Berkas SPJ & PDF</span>
                    </div>
                    <h2 class="text-lg md:text-xl font-extrabold text-slate-900 tracking-tight">Profil & Tanda Tangan Pimpinan</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Kegiatan: <span class="font-bold text-slate-800" x-text="pimpinanEventTitle"></span></p>
                </div>
                <button type="button" @click="pimpinanModalOpen = false" class="p-1.5 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="'/super-admin/events/' + pimpinanEventId + '/pimpinan'" method="POST" @submit="submitPimpinanForm($event)" class="space-y-4">
                @csrf

                <!-- Grid Data Pimpinan & Titimangsa (2 Kolom Berdampingan) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Nama Pimpinan (Beserta Gelar) <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="pimpinan_name" 
                               x-model="pimpinanName" 
                               required 
                               placeholder="Dra. ATIK YULIHARTI, M.Kes" 
                               class="form-input py-2 px-3 text-xs rounded-xl w-full border-slate-300 font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            NIP / NIK <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="pimpinan_nip" 
                               x-model="pimpinanNip" 
                               required 
                               placeholder="196807201991032001" 
                               class="form-input py-2 px-3 text-xs rounded-xl w-full border-slate-300 font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Jabatan Penandatangan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="pimpinan_position" 
                               x-model="pimpinanPosition" 
                               required 
                               placeholder="Ketua Tim Kerja Pelayanan Publik dan PZI" 
                               class="form-input py-2 px-3 text-xs rounded-xl w-full border-slate-300 font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Tempat & Tanggal
                        </label>
                        <input type="text" 
                               name="pimpinan_date_text" 
                               x-model="pimpinanDateText" 
                               placeholder="Jakarta, 14 Oktober 2026" 
                               class="form-input py-2 px-3 text-xs rounded-xl w-full border-slate-300 font-medium">
                    </div>
                </div>

                <!-- Area Tanda Tangan Digital -->
                <div class="pt-1">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                            <span>Goreskan Tanda Tangan Digital <span class="text-rose-500">*</span></span>
                        </label>

                        <div class="flex items-center gap-2">
                            <template x-if="existingSignature && isCanvasClean">
                                <span class="text-[10px] text-emerald-700 font-bold bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full flex items-center gap-1 shadow-2xs">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    TTD Tersimpan Aktif
                                </span>
                            </template>

                            <button type="button" 
                                    @click="clearPad" 
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold text-rose-600 bg-rose-50 border border-rose-200 hover:bg-rose-100 transition-colors cursor-pointer">
                                <svg class="w-3 h-3 text-rose-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <span>Bersihkan Kotak</span>
                            </button>
                        </div>
                    </div>

                    <!-- Canvas Box -->
                    <div class="rounded-2xl border-2 border-dashed border-cyan-400 bg-slate-50/20 relative overflow-hidden select-none shadow-2xs">
                        <canvas x-ref="superAdminPimpinanCanvas" class="w-full h-36 block bg-white cursor-crosshair" style="touch-action: none;"></canvas>


                        <template x-if="existingSignature && isCanvasClean">
                            <div class="absolute inset-0 bg-white/95 backdrop-blur-2xs flex flex-col items-center justify-center p-3 pointer-events-none select-none">
                                <img :src="existingSignature" alt="Tanda Tangan Tersimpan" class="h-20 object-contain max-w-full">
                                <span class="text-[10px] text-slate-400 mt-1 font-medium">Tanda tangan sebelumnya tersimpan. Klik "Bersihkan Kotak" jika ingin membuat ulang.</span>
                            </div>
                        </template>
                    </div>

                    <input type="hidden" name="pimpinan_signature" x-ref="superAdminSignatureInput">
                </div>


                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="pimpinanModalOpen = false" class="btn-secondary py-2 px-5 text-xs font-bold rounded-xl">Batal</button>
                    <button type="submit" class="btn-primary py-2 px-6 text-xs font-bold rounded-xl shadow-md shadow-brand-500/20 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Simpan Pengesahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function eventStatusToggle(config) {
    return {
        eventId: config.eventId,
        isOpen: config.initialStatus === 'APPROVED',
        badgeClass: config.initialBadgeClass,
        badgeLabel: config.initialBadgeLabel,
        toggleUrl: config.toggleUrl,
        isLoading: false,

        async toggle() {
            if (this.isLoading) return;
            this.isLoading = true;

            const previousOpen = this.isOpen;
            const previousBadgeLabel = this.badgeLabel;
            const previousBadgeClass = this.badgeClass;

            // Optimistic update: langsung ubah UI seketika
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.badgeLabel = 'Disetujui';
                this.badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200 ring-emerald-600/20';
            } else {
                this.badgeLabel = 'Selesai / Ditutup';
                this.badgeClass = 'bg-slate-50 text-slate-700 border-slate-200 ring-slate-600/20';
            }

            try {
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

                const response = await fetch(this.toggleUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }

                const data = await response.json();
                if (data && data.success) {
                    this.isOpen = Boolean(data.isOpen);
                    this.badgeLabel = data.badgeLabel;
                    this.badgeClass = data.badgeClass;
                } else {
                    throw new Error((data && data.message) || 'Gagal mengubah status agenda');
                }
            } catch (err) {
                console.warn('AJAX toggle gagal, mengirimkan form langsung...', err);
                const form = document.getElementById('toggle-form-' + this.eventId);
                if (form) {
                    form.submit();
                    return;
                }
                // Kembalikan ke state semula jika form tidak ditemukan
                this.isOpen = previousOpen;
                this.badgeLabel = previousBadgeLabel;
                this.badgeClass = previousBadgeClass;
                alert('Gagal mengubah status agenda: ' + (err.message || 'Kesalahan jaringan'));
            } finally {
                this.isLoading = false;
            }
        }
    };
}
</script>
@endsection
