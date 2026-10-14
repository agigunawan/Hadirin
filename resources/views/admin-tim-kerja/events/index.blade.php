@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="pimpinanModalManager()">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-black tracking-tight text-slate-900">Agenda Kegiatan Tim Kerja</h1>
                @if(auth()->user()->isSuperAdmin())
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200 uppercase tracking-wider">
                        Semua Tim Kerja
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-1">
                @if(auth()->user()->isSuperAdmin())
                    Daftar seluruh agenda dan reservasi ruang rapat dari semua tim kerja dinas.
                @else
                    Daftar reservasi ruang rapat dan penayangan kode QR presensi untuk tim kerja Anda.
                @endif
            </p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            @if(auth()->user()->isSuperAdmin() && $workUnits->isNotEmpty())
                <form method="GET" action="{{ route('admin-tim-kerja.events.index') }}" class="flex items-center">
                    <select name="work_unit" onchange="this.form.submit()" class="form-input py-2 px-3 text-xs rounded-xl bg-white border-slate-300">
                        <option value="">-- Semua Tim Kerja --</option>
                        @foreach($workUnits as $unit)
                            <option value="{{ $unit }}" {{ $selectedWorkUnit === $unit ? 'selected' : '' }}>
                                {{ $unit }}
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif

            <a href="{{ route('admin-tim-kerja.events.create') }}" class="btn-primary py-3 px-5 text-xs font-bold rounded-xl shadow-lg shadow-brand-500/25 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span>Pengajuan Agenda Baru (H-1)</span>
            </a>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-400 uppercase tracking-wider text-[10px]">
                        <th class="py-3.5 px-4">Nama Kegiatan & Tim Kerja</th>
                        <th class="py-3.5 px-4">Ruang Rapat</th>
                        <th class="py-3.5 px-4">Tanggal & Jam</th>
                        <th class="py-3.5 px-4">Status Agenda</th>
                        <th class="py-3.5 px-4 text-center">Kehadiran</th>
                        <th class="py-3.5 px-4 text-right">Aksi Penyelenggara</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($events as $event)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-4 px-4">
                                <div class="font-bold text-slate-900 max-w-sm">{{ $event->title }}</div>
                                <div class="text-[11px] text-emerald-700 font-medium flex items-center gap-1.5 mt-0.5">
                                    <svg class="w-3 h-3 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    <span class="truncate">{{ $event->work_unit }}</span>
                                    @if(auth()->user()->isSuperAdmin() && $event->creator)
                                        <span class="text-slate-400 font-normal">• {{ $event->creator->name }}</span>
                                    @endif
                                </div>
                                @if($event->rejection_note)
                                    <div class="mt-1.5 p-2 rounded-xl bg-rose-50 text-rose-700 text-[11px] border border-rose-200 max-w-xs">
                                        <span class="font-bold">Catatan Penolakan:</span> {{ $event->rejection_note }}
                                    </div>
                                @endif
                            </td>

                            <td class="py-4 px-4 font-semibold text-slate-800">
                                {{ $event->room->name }}
                            </td>

                            <td class="py-4 px-4">
                                <div class="font-medium text-slate-800">{{ $event->event_date->translatedFormat('d M Y') }}</div>
                                <div class="text-[11px] text-slate-500 font-mono">{{ $event->formatted_time }}</div>
                            </td>

                            <td class="py-4 px-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $event->status->badgeClass() }}">
                                    {{ $event->status->label() }}
                                </span>
                            </td>

                            <td class="py-4 px-4 text-center">
                                <span class="font-extrabold text-sm text-slate-900">{{ $event->attendees_count }}</span>
                                <span class="text-[10px] text-slate-400 block">Peserta</span>
                            </td>

                            <td class="py-4 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if(in_array($event->status->value, ['APPROVED', 'CLOSED']))
                                        <!-- Open Display Mode (Projector) -->
                                        <a href="{{ route('display.show', ['token' => $event->qr_token]) }}" 
                                           target="_blank" 
                                           title="Buka Layar Proyektor 16:9"
                                           class="px-3 py-1.5 rounded-xl bg-slate-900 text-white hover:bg-slate-800 text-[11px] font-bold shadow-2xs transition-colors flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                            <span>Tayang Proyektor</span>
                                        </a>

                                        <!-- Monitor Attendees -->
                                        <a href="{{ route('admin-tim-kerja.events.monitor', $event) }}" 
                                           title="Pantau Peserta Presensi"
                                           class="px-3 py-1.5 rounded-xl bg-brand-50 hover:bg-brand-100 text-brand-700 text-[11px] font-bold border border-brand-200 transition-colors">
                                            Pantau Peserta
                                        </a>

                                        <!-- SPJ Excel Download -->
                                        <a href="{{ route('admin-tim-kerja.events.export', $event) }}" 
                                           title="Unduh Berkas SPJ Excel"
                                           class="p-2 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-600 transition-colors">
                                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </a>

                                        <!-- SPJ PDF Download -->
                                        <a href="{{ route('admin-tim-kerja.events.export-pdf', $event) }}" 
                                           title="Unduh Berkas SPJ PDF"
                                           class="p-2 rounded-xl border border-slate-200 hover:bg-rose-50 hover:border-rose-300 text-slate-600 transition-colors">
                                            <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-6 4h4"/></svg>
                                        </a>

                                        <!-- Profil & Tanda Tangan Pimpinan Pengesahan (Gambar 1 & Gambar 2) -->
                                        <button type="button" 
                                                @click="openModal({
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

                                        <!-- Edit & Field Configuration -->
                                        <a href="{{ route('admin-tim-kerja.events.edit', $event) }}" 
                                           title="Atur Kolom Presensi & Detail Agenda"
                                           class="p-2 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-600 transition-colors flex items-center justify-center">
                                            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                    @endif

                                    @if(in_array($event->status->value, ['PENDING', 'REJECTED']))
                                        <form method="POST" action="{{ route('admin-tim-kerja.events.destroy', $event) }}" onsubmit="return confirm('Hapus pengajuan agenda ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-xl transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400 text-xs">
                                @if(auth()->user()->isSuperAdmin())
                                    Tidak ada agenda kegiatan tim kerja yang ditemukan.
                                @else
                                    Tim kerja Anda belum mengajukan agenda rapat.
                                @endif
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

    <!-- Modal Profil & Tanda Tangan Pimpinan -->
    <div x-show="modalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         style="display: none;">
        <div class="bg-white rounded-3xl max-w-2xl w-full p-6 md:p-7 shadow-2xl border border-slate-200 overflow-y-auto max-h-[92vh]" 
             @click.away="closeModal">
            
            <!-- Modal Header -->
            <div class="flex items-start justify-between gap-4 pb-4 mb-4 border-b border-slate-100">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-[10px] font-bold uppercase tracking-wider mb-1.5">
                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>Pengesahan Berkas SPJ & PDF</span>
                    </div>
                    <h2 class="text-lg md:text-xl font-extrabold text-slate-900 tracking-tight">Profil & Tanda Tangan Pimpinan</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Kegiatan: <span class="font-bold text-slate-800" x-text="activeEventTitle"></span></p>
                </div>
                <button type="button" @click="closeModal" class="p-1.5 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="'/admin-tim-kerja/events/' + activeEventId + '/pimpinan'" method="POST" @submit="submitForm($event)" class="space-y-4">
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
                        <canvas x-ref="pimpinanCanvas" class="w-full h-36 block bg-white cursor-crosshair" style="touch-action: none;"></canvas>


                        <template x-if="existingSignature && isCanvasClean">
                            <div class="absolute inset-0 bg-white/95 backdrop-blur-2xs flex flex-col items-center justify-center p-3 pointer-events-none select-none">
                                <img :src="existingSignature" alt="Tanda Tangan Tersimpan" class="h-20 object-contain max-w-full">
                                <span class="text-[10px] text-slate-400 mt-1 font-medium">Tanda tangan sebelumnya tersimpan. Klik "Bersihkan Kotak" jika ingin membuat ulang.</span>
                            </div>
                        </template>
                    </div>

                    <input type="hidden" name="pimpinan_signature" x-ref="pimpinanSignatureInput">
                </div>


                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="closeModal" class="btn-secondary py-2 px-5 text-xs font-bold rounded-xl">Batal</button>
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
function pimpinanModalManager() {
    return {
        modalOpen: false,
        activeEventId: null,
        activeEventTitle: '',
        pimpinanName: '',
        pimpinanNip: '',
        pimpinanPosition: '',
        pimpinanDateText: '',
        existingSignature: '',
        isCanvasClean: true,
        pad: null,

        init() {
            this.$watch('modalOpen', (val) => {
                if (val) {
                    this.$nextTick(() => {
                        const canvas = this.$refs.pimpinanCanvas;
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
                }
            });
        },

        openModal(data) {
            this.activeEventId = data.id;
            this.activeEventTitle = data.title;
            this.pimpinanName = data.name || '';
            this.pimpinanNip = data.nip || '';
            this.pimpinanPosition = data.position || '';
            this.pimpinanDateText = data.date_text || '';
            this.existingSignature = data.signature || '';
            this.isCanvasClean = true;
            this.modalOpen = true;
        },

        closeModal() {
            this.modalOpen = false;
            if (this.pad) {
                this.pad.clear();
            }
        },

        clearPad() {
            if (this.pad) {
                this.pad.clear();
            }
            this.existingSignature = '';
            this.isCanvasClean = false;
        },

        submitForm(e) {
            const hasDrawn = this.pad && !this.pad.isEmpty();
            if (hasDrawn) {
                this.$refs.pimpinanSignatureInput.value = this.pad.toDataURL('image/png');
            } else if (this.existingSignature) {
                this.$refs.pimpinanSignatureInput.value = this.existingSignature;
            } else {
                e.preventDefault();
                alert('Mohon goreskan tanda tangan pimpinan pada kotak yang disediakan.');
                return false;
            }
        }
    };
}
</script>
@endsection
