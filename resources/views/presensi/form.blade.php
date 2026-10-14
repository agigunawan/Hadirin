@extends('layouts.guest')

@section('content')
<div class="space-y-6">
    <!-- Meeting Overview Card -->
    <div class="bg-gradient-to-br from-slate-900 via-slate-900 to-emerald-950 text-white rounded-3xl p-6 shadow-xl border border-white/10 relative overflow-hidden">
        <div class="absolute -right-8 -bottom-8 w-40 h-40 bg-brand-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-semibold mb-3 border border-emerald-500/30">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
            Presensi Dibuka
        </div>

        <h2 class="text-xl md:text-2xl font-black tracking-tight leading-snug text-white">
            {{ $event->title }}
        </h2>

        <div class="mt-4 pt-4 border-t border-white/10 grid grid-cols-2 gap-3 text-xs text-slate-300">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-brand-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span class="truncate">{{ $event->room->name }}</span>
            </div>
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-brand-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ $event->formatted_time }}</span>
            </div>
            <div class="flex items-center gap-2 col-span-2">
                <svg class="w-4 h-4 text-brand-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>{{ $event->event_date->translatedFormat('l, d F Y') }}</span>
            </div>
        </div>
    </div>

    <!-- Presensi Form -->
    <div class="bg-white rounded-3xl p-6 md:p-8 shadow-xl border border-slate-200/80" 
         x-data="{
             hasTransport: {{ old('has_transport', 'false') ? 'true' : 'false' }},
             gpsStatus: 'idle',
             gpsMessage: '',
             coordinates: '{{ old('coordinates', '') }}',
             pad: null,
             initPad() {
                 this.$nextTick(() => {
                     const canvas = this.$refs.signatureCanvas;
                     if (canvas && window.initSignaturePad) {
                         this.pad = window.initSignaturePad(canvas);
                     }
                 });
             },
             clearPad() {
                 if (this.pad) {
                     this.pad.clear();
                 }
             },
             fetchGps() {
                 this.gpsStatus = 'loading';
                 this.gpsMessage = 'Mencari sinyal GPS...';
                 if (window.getCurrentCoordinates) {
                     window.getCurrentCoordinates()
                         .then(coords => {
                             this.coordinates = coords;
                             this.gpsStatus = 'success';
                             this.gpsMessage = 'Titik lokasi berhasil dideteksi.';
                         })
                         .catch(err => {
                             this.gpsStatus = 'error';
                             this.gpsMessage = err.message || 'Gagal mendeteksi lokasi di perangkat ini.';
                         });
                 } else {
                     this.gpsStatus = 'error';
                     this.gpsMessage = 'Geolocation tidak didukung browser ini.';
                 }
             },
             useDefaultOfficeGps() {
                 this.coordinates = '-6.107738, 106.881249';
                 this.gpsStatus = 'success';
                 this.gpsMessage = 'Titik kantor BBKK Tanjung Priok disetel.';
             },
             submitForm(e) {
                 if (!this.pad || this.pad.isEmpty()) {
                     e.preventDefault();
                     alert('Mohon bubuhkan tanda tangan Anda pada kotak kanvas yang telah disediakan.');
                     return false;
                 }
                 this.$refs.signatureInput.value = this.pad.toDataURL('image/png');
             }
         }"
         x-init="initPad()">

        <div class="mb-6">
            <h3 class="text-lg font-extrabold text-slate-900 tracking-tight">Formulir Kehadiran Peserta</h3>
            <p class="text-xs text-slate-500 mt-0.5">Mohon lengkapi identitas kedinasan Anda di bawah ini secara benar.</p>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                <div class="font-bold mb-1">Periksa isian Anda:</div>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('presensi.store', ['token' => $token]) }}" @submit="submitForm($event)" class="space-y-5">
            @csrf
            <input type="hidden" name="coordinates" x-model="coordinates">
            <input type="hidden" name="signature_data" x-ref="signatureInput">


            @if($event->isFieldActive('full_name'))
                <!-- Nama Lengkap & Gelar -->
                <div>
                    <label for="full_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nama Lengkap & Gelar <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           id="full_name" 
                           name="full_name" 
                           value="{{ old('full_name') }}" 
                           required 
                           placeholder="Contoh: dr. Budi Santoso, Sp.OK" 
                           class="form-input py-2.5 text-sm rounded-xl">
                </div>
            @endif

            @if($event->isFieldActive('nip_or_id'))
                <!-- NIP / NIK -->
                <div>
                    <label for="nip_or_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        NIP / NIK Pegawai <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           id="nip_or_id" 
                           name="nip_or_id" 
                           value="{{ old('nip_or_id') }}" 
                           required 
                           placeholder="18 digit NIP untuk ASN atau 16 digit NIK" 
                           class="form-input py-2.5 text-sm rounded-xl font-mono">
                </div>
            @endif

            @if($event->isFieldActive('gender'))
                <!-- Jenis Kelamin -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Jenis Kelamin <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50/50 cursor-pointer hover:bg-white text-xs font-medium">
                            <input type="radio" name="gender" value="LAKI_LAKI" {{ old('gender', 'LAKI_LAKI') === 'LAKI_LAKI' ? 'checked' : '' }} class="text-brand-600 focus:ring-brand-500">
                            <span>Laki-laki</span>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50/50 cursor-pointer hover:bg-white text-xs font-medium">
                            <input type="radio" name="gender" value="PEREMPUAN" {{ old('gender') === 'PEREMPUAN' ? 'checked' : '' }} class="text-brand-600 focus:ring-brand-500">
                            <span>Perempuan</span>
                        </label>
                    </div>
                </div>
            @endif

            @if($event->isFieldActive('agency') || $event->isFieldActive('position'))
                <!-- Instansi & Jabatan -->
                <div class="grid grid-cols-1 {{ ($event->isFieldActive('agency') && $event->isFieldActive('position')) ? 'md:grid-cols-2' : '' }} gap-4">
                    @if($event->isFieldActive('agency'))
                        <div>
                            <label for="agency" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Instansi / Unit Asal <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   id="agency" 
                                   name="agency" 
                                   value="{{ old('agency') }}" 
                                   required 
                                   placeholder="Contoh: BBKK Tanjung Priok / Ditjen P2P" 
                                   class="form-input py-2.5 text-sm rounded-xl">
                        </div>
                    @endif

                    @if($event->isFieldActive('position'))
                        <div>
                            <label for="position" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Jabatan Kedinasan <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   id="position" 
                                   name="position" 
                                   value="{{ old('position') }}" 
                                   required 
                                   placeholder="Contoh: Epidemiolog Kesehatan Ahli Muda" 
                                   class="form-input py-2.5 text-sm rounded-xl">
                        </div>
                    @endif
                </div>
            @endif

            @if($event->isFieldActive('phone') || $event->isFieldActive('address'))
                <!-- Kontak HP & Alamat -->
                <div class="grid grid-cols-1 {{ ($event->isFieldActive('phone') && $event->isFieldActive('address')) ? 'md:grid-cols-2' : '' }} gap-4">
                    @if($event->isFieldActive('phone'))
                        <div>
                            <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Nomor HP / WhatsApp <span class="text-rose-500">*</span>
                            </label>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone') }}" 
                                   required 
                                   placeholder="08xxxxxxxxxx" 
                                   class="form-input py-2.5 text-sm rounded-xl font-mono">
                        </div>
                    @endif

                    @if($event->isFieldActive('address'))
                        <div>
                            <label for="address" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Alamat Asal / Kantor <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   id="address" 
                                   name="address" 
                                   value="{{ old('address') }}" 
                                   required 
                                   placeholder="Kota / Alamat Instansi" 
                                   class="form-input py-2.5 text-sm rounded-xl">
                        </div>
                    @endif
                </div>
            @endif

            @if($event->isFieldActive('coordinates'))
                <!-- Geolocation Capture -->
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 space-y-2.5">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-slate-800 flex items-center gap-2">
                                    <span>Koordinat Lokasi GPS <span class="text-rose-500">*</span></span>
                                </div>
                                <div class="text-[11px] text-slate-500" x-text="coordinates ? 'Tercatat: ' + coordinates : 'Wajib verifikasi kehadiran di lokasi'"></div>
                            </div>
                        </div>

                        <button type="button" 
                                @click="fetchGps" 
                                :disabled="gpsStatus === 'loading'"
                                class="px-3 py-1.5 text-xs font-bold rounded-xl border border-slate-300 bg-white hover:bg-slate-100 transition-colors flex items-center gap-1.5 text-slate-700 shadow-2xs disabled:opacity-60 cursor-pointer">
                            <span x-show="gpsStatus === 'loading'" class="w-3 h-3 border-2 border-brand-500 border-t-transparent rounded-full animate-spin"></span>
                            <span x-text="coordinates ? 'Perbarui Lokasi' : (gpsStatus === 'loading' ? 'Mencari...' : 'Deteksi GPS')"></span>
                        </button>
                    </div>

                    <!-- Fallback / Error Alert & Default Office Helper -->
                    <div x-show="gpsStatus === 'error'" x-transition class="p-2.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-[11px] space-y-1.5">
                        <div class="flex items-start gap-1.5">
                            <svg class="w-3.5 h-3.5 text-amber-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span x-text="gpsMessage || 'GPS tidak terdeteksi pada perangkat ini.'"></span>
                        </div>
                        <div class="flex items-center gap-2 pt-0.5">
                            <button type="button" @click="useDefaultOfficeGps" class="font-bold underline hover:text-amber-950 cursor-pointer">
                                Gunakan Titik Kantor BBKK Priok
                            </button>
                            <span>•</span>
                            <button type="button" @click="gpsStatus = 'idle'" class="text-slate-500 hover:text-slate-700 cursor-pointer">
                                Abaikan
                            </button>
                        </div>
                    </div>

                    <!-- Input Field if GPS Filled -->
                    <div x-show="coordinates" class="flex items-center gap-2 pt-0.5">
                        <input type="text" x-model="coordinates" placeholder="Contoh: -6.1077, 106.8812" class="form-input py-1 px-2.5 text-xs rounded-lg font-mono flex-1 bg-white">
                        <button type="button" @click="coordinates = ''; gpsStatus = 'idle'" class="text-[11px] text-rose-600 hover:underline px-1 cursor-pointer">Reset</button>
                    </div>
                </div>
            @endif

            <!-- Conditional Transport Checkbox -->
            @if($event->isFieldActive('transport'))
                <div class="pt-2">
                    <label class="flex items-start gap-3 p-3.5 rounded-2xl border border-slate-200 bg-slate-50/60 cursor-pointer hover:bg-slate-50 transition-colors">
                        <input type="checkbox" name="has_transport" value="1" x-model="hasTransport" class="mt-0.5 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        <div>
                            <div class="text-xs font-bold text-slate-800">Ada Penggantian Biaya Transport Kedinasan</div>
                            <div class="text-[11px] text-slate-500 mt-0.5">Centang jika acara ini menyediakan SPJ uang transport untuk rekening perbankan Anda.</div>
                        </div>
                    </label>
                </div>

                <!-- Conditional Bank Details -->
                <div x-show="hasTransport" x-collapse class="space-y-4 p-4 rounded-2xl bg-amber-50/60 border border-amber-200/80">
                    <div class="text-xs font-bold text-amber-900 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        <span>Informasi Rekening Bank Penerima</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label for="bank_name" class="block text-[11px] font-bold text-slate-700 mb-1">Nama Bank</label>
                            <input type="text" id="bank_name" name="bank_name" value="{{ old('bank_name') }}" placeholder="Contoh: BRI / Mandiri / BNI" class="form-input py-2 text-xs rounded-xl">
                        </div>
                        <div>
                            <label for="bank_account_no" class="block text-[11px] font-bold text-slate-700 mb-1">Nomor Rekening</label>
                            <input type="text" id="bank_account_no" name="bank_account_no" value="{{ old('bank_account_no') }}" placeholder="Nomor rekening bank" class="form-input py-2 text-xs rounded-xl font-mono">
                        </div>
                        <div>
                            <label for="bank_account_name" class="block text-[11px] font-bold text-slate-700 mb-1">Atas Nama Rekening</label>
                            <input type="text" id="bank_account_name" name="bank_account_name" value="{{ old('bank_account_name') }}" placeholder="Sesuai buku tabungan" class="form-input py-2 text-xs rounded-xl">
                        </div>
                    </div>
                </div>
            @endif

            <!-- E-Signature Canvas -->
            <div class="pt-2">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Tanda Tangan Digital <span class="text-rose-500">*</span>
                    </label>
                    <button type="button" @click="clearPad" class="text-xs text-rose-600 hover:text-rose-700 font-semibold flex items-center gap-1 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>Hapus / Ulangi</span>
                    </button>
                </div>

                <div class="rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 relative overflow-hidden group select-none">
                    <canvas x-ref="signatureCanvas" class="signature-pad-canvas w-full h-44 block bg-white cursor-crosshair" style="touch-action: none;"></canvas>
                    <div class="absolute bottom-2 right-3 text-[10px] text-slate-400 font-medium pointer-events-none select-none">
                        Goreskan tanda tangan di sini
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4">
                <button type="submit" class="w-full btn-primary py-4 text-base font-bold rounded-2xl shadow-xl shadow-brand-600/25">
                    Kirim & Lanjutkan ke Survei
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
