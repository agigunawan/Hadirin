<div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5 md:p-6 space-y-4 shadow-2xs"
     x-data="{
         checked: {
             full_name: {{ ($resolvedConfigFields['full_name']['enabled'] ?? true) ? 'true' : 'false' }},
             nip_or_id: {{ ($resolvedConfigFields['nip_or_id']['enabled'] ?? true) ? 'true' : 'false' }},
             gender: {{ ($resolvedConfigFields['gender']['enabled'] ?? true) ? 'true' : 'false' }},
             agency: {{ ($resolvedConfigFields['agency']['enabled'] ?? true) ? 'true' : 'false' }},
             position: {{ ($resolvedConfigFields['position']['enabled'] ?? true) ? 'true' : 'false' }},
             phone: {{ ($resolvedConfigFields['phone']['enabled'] ?? true) ? 'true' : 'false' }},
             address: {{ ($resolvedConfigFields['address']['enabled'] ?? true) ? 'true' : 'false' }},
             coordinates: {{ ($resolvedConfigFields['coordinates']['enabled'] ?? false) ? 'true' : 'false' }},
             transport: {{ ($resolvedConfigFields['transport']['enabled'] ?? false) ? 'true' : 'false' }}
         }
     }">
    <div class="border-b border-slate-200/80 pb-3">
        <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-brand-500 animate-pulse"></span>
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-800">
                Pengaturan Kolom Formulir Presensi Tamu
            </h3>
        </div>
        <p class="text-[11px] text-slate-500 mt-1">
            Centang isian kolom yang ingin <strong class="text-emerald-700">dimunculkan pada formulir presensi peserta</strong>. Kolom yang tidak dicentang <span class="text-slate-600 font-semibold">tidak akan dimunculkan</span>.
        </p>
    </div>

    <!-- Field Cards Grid: Single Checkbox per Column -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
        <!-- 1. Nama Lengkap & Gelar -->
        <label class="p-3.5 rounded-2xl border cursor-pointer transition-all flex items-start gap-3 select-none"
               :class="checked.full_name ? 'bg-emerald-50/60 border-emerald-300 ring-2 ring-emerald-500/10 shadow-xs' : 'bg-white border-slate-200 hover:border-slate-300'">
            <input type="checkbox" 
                   name="config_fields[full_name]" 
                   value="1" 
                   x-model="checked.full_name"
                   class="mt-0.5 rounded-lg border-slate-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4 cursor-pointer">
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-bold text-slate-900 flex items-center gap-1">
                        <span>Nama Lengkap & Gelar</span>
                        <span x-show="checked.full_name" class="text-rose-500 font-black">*</span>
                    </span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider"
                          :class="checked.full_name ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500'">
                        <span x-text="checked.full_name ? 'Muncul' : 'Tidak Muncul'"></span>
                    </span>
                </div>
                <p class="text-[11px] text-slate-500 mt-0.5">Nama lengkap beserta gelar kedinasan peserta.</p>
            </div>
        </label>

        <!-- 2. NIP / NIK Pegawai -->
        <label class="p-3.5 rounded-2xl border cursor-pointer transition-all flex items-start gap-3 select-none"
               :class="checked.nip_or_id ? 'bg-emerald-50/60 border-emerald-300 ring-2 ring-emerald-500/10 shadow-xs' : 'bg-white border-slate-200 hover:border-slate-300'">
            <input type="checkbox" 
                   name="config_fields[nip_or_id]" 
                   value="1" 
                   x-model="checked.nip_or_id"
                   class="mt-0.5 rounded-lg border-slate-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4 cursor-pointer">
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-bold text-slate-900 flex items-center gap-1">
                        <span>NIP / NIK Pegawai</span>
                        <span x-show="checked.nip_or_id" class="text-rose-500 font-black">*</span>
                    </span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider"
                          :class="checked.nip_or_id ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500'">
                        <span x-text="checked.nip_or_id ? 'Muncul' : 'Tidak Muncul'"></span>
                    </span>
                </div>
                <p class="text-[11px] text-slate-500 mt-0.5">18 digit NIP untuk ASN atau 16 digit NIK peserta.</p>
            </div>
        </label>

        <!-- 3. Jenis Kelamin -->
        <label class="p-3.5 rounded-2xl border cursor-pointer transition-all flex items-start gap-3 select-none"
               :class="checked.gender ? 'bg-emerald-50/60 border-emerald-300 ring-2 ring-emerald-500/10 shadow-xs' : 'bg-white border-slate-200 hover:border-slate-300'">
            <input type="checkbox" 
                   name="config_fields[gender]" 
                   value="1" 
                   x-model="checked.gender"
                   class="mt-0.5 rounded-lg border-slate-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4 cursor-pointer">
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-bold text-slate-900 flex items-center gap-1">
                        <span>Jenis Kelamin</span>
                        <span x-show="checked.gender" class="text-rose-500 font-black">*</span>
                    </span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider"
                          :class="checked.gender ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500'">
                        <span x-text="checked.gender ? 'Muncul' : 'Tidak Muncul'"></span>
                    </span>
                </div>
                <p class="text-[11px] text-slate-500 mt-0.5">Pilihan radio Laki-laki / Perempuan.</p>
            </div>
        </label>

        <!-- 4. Instansi / Unit Asal -->
        <label class="p-3.5 rounded-2xl border cursor-pointer transition-all flex items-start gap-3 select-none"
               :class="checked.agency ? 'bg-emerald-50/60 border-emerald-300 ring-2 ring-emerald-500/10 shadow-xs' : 'bg-white border-slate-200 hover:border-slate-300'">
            <input type="checkbox" 
                   name="config_fields[agency]" 
                   value="1" 
                   x-model="checked.agency"
                   class="mt-0.5 rounded-lg border-slate-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4 cursor-pointer">
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-bold text-slate-900 flex items-center gap-1">
                        <span>Instansi / Unit Asal</span>
                        <span x-show="checked.agency" class="text-rose-500 font-black">*</span>
                    </span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider"
                          :class="checked.agency ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500'">
                        <span x-text="checked.agency ? 'Muncul' : 'Tidak Muncul'"></span>
                    </span>
                </div>
                <p class="text-[11px] text-slate-500 mt-0.5">Asal instansi kantor kedinasan atau unit seksi kerja.</p>
            </div>
        </label>

        <!-- 5. Jabatan Kedinasan -->
        <label class="p-3.5 rounded-2xl border cursor-pointer transition-all flex items-start gap-3 select-none"
               :class="checked.position ? 'bg-emerald-50/60 border-emerald-300 ring-2 ring-emerald-500/10 shadow-xs' : 'bg-white border-slate-200 hover:border-slate-300'">
            <input type="checkbox" 
                   name="config_fields[position]" 
                   value="1" 
                   x-model="checked.position"
                   class="mt-0.5 rounded-lg border-slate-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4 cursor-pointer">
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-bold text-slate-900 flex items-center gap-1">
                        <span>Jabatan Kedinasan</span>
                        <span x-show="checked.position" class="text-rose-500 font-black">*</span>
                    </span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider"
                          :class="checked.position ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500'">
                        <span x-text="checked.position ? 'Muncul' : 'Tidak Muncul'"></span>
                    </span>
                </div>
                <p class="text-[11px] text-slate-500 mt-0.5">Jabatan struktural / fungsional kedinasan peserta.</p>
            </div>
        </label>

        <!-- 6. Nomor HP / WhatsApp -->
        <label class="p-3.5 rounded-2xl border cursor-pointer transition-all flex items-start gap-3 select-none"
               :class="checked.phone ? 'bg-emerald-50/60 border-emerald-300 ring-2 ring-emerald-500/10 shadow-xs' : 'bg-white border-slate-200 hover:border-slate-300'">
            <input type="checkbox" 
                   name="config_fields[phone]" 
                   value="1" 
                   x-model="checked.phone"
                   class="mt-0.5 rounded-lg border-slate-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4 cursor-pointer">
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-bold text-slate-900 flex items-center gap-1">
                        <span>Nomor HP / WhatsApp</span>
                        <span x-show="checked.phone" class="text-rose-500 font-black">*</span>
                    </span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider"
                          :class="checked.phone ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500'">
                        <span x-text="checked.phone ? 'Muncul' : 'Tidak Muncul'"></span>
                    </span>
                </div>
                <p class="text-[11px] text-slate-500 mt-0.5">Nomor WhatsApp aktif untuk koordinasi kedinasan.</p>
            </div>
        </label>

        <!-- 7. Alamat Asal / Kantor -->
        <label class="p-3.5 rounded-2xl border cursor-pointer transition-all flex items-start gap-3 select-none"
               :class="checked.address ? 'bg-emerald-50/60 border-emerald-300 ring-2 ring-emerald-500/10 shadow-xs' : 'bg-white border-slate-200 hover:border-slate-300'">
            <input type="checkbox" 
                   name="config_fields[address]" 
                   value="1" 
                   x-model="checked.address"
                   class="mt-0.5 rounded-lg border-slate-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4 cursor-pointer">
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-bold text-slate-900 flex items-center gap-1">
                        <span>Alamat Asal / Kantor</span>
                        <span x-show="checked.address" class="text-rose-500 font-black">*</span>
                    </span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider"
                          :class="checked.address ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500'">
                        <span x-text="checked.address ? 'Muncul' : 'Tidak Muncul'"></span>
                    </span>
                </div>
                <p class="text-[11px] text-slate-500 mt-0.5">Kota domisili atau alamat instansi peserta.</p>
            </div>
        </label>

        <!-- 8. Koordinat Lokasi GPS -->
        <label class="p-3.5 rounded-2xl border cursor-pointer transition-all flex items-start gap-3 select-none"
               :class="checked.coordinates ? 'bg-emerald-50/60 border-emerald-300 ring-2 ring-emerald-500/10 shadow-xs' : 'bg-white border-slate-200 hover:border-slate-300'">
            <input type="checkbox" 
                   name="config_fields[coordinates]" 
                   value="1" 
                   x-model="checked.coordinates"
                   class="mt-0.5 rounded-lg border-slate-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4 cursor-pointer">
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-bold text-slate-900 flex items-center gap-1">
                        <span>Koordinat Lokasi GPS</span>
                        <span x-show="checked.coordinates" class="text-rose-500 font-black">*</span>
                    </span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider"
                          :class="checked.coordinates ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500'">
                        <span x-text="checked.coordinates ? 'Muncul' : 'Tidak Muncul'"></span>
                    </span>
                </div>
                <p class="text-[11px] text-slate-500 mt-0.5">Deteksi GPS verifikasi kehadiran peserta di lokasi.</p>
            </div>
        </label>

        <!-- 9. Ada Penggantian Biaya Transport Kedinasan -->
        <label class="p-3.5 rounded-2xl border cursor-pointer transition-all flex items-start gap-3 select-none md:col-span-2"
               :class="checked.transport ? 'bg-emerald-50/60 border-emerald-300 ring-2 ring-emerald-500/10 shadow-xs' : 'bg-white border-slate-200 hover:border-slate-300'">
            <input type="checkbox" 
                   name="config_fields[transport]" 
                   value="1" 
                   x-model="checked.transport"
                   class="mt-0.5 rounded-lg border-slate-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4 cursor-pointer">
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-bold text-slate-900 flex items-center gap-1">
                        <span>Ada Penggantian Biaya Transport Kedinasan</span>
                        <span x-show="checked.transport" class="text-rose-500 font-black">*</span>
                    </span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider"
                          :class="checked.transport ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500'">
                        <span x-text="checked.transport ? 'Muncul' : 'Tidak Muncul'"></span>
                    </span>
                </div>
                <p class="text-[11px] text-slate-500 mt-0.5">Centang jika acara ini menyediakan SPJ uang transport untuk rekening perbankan tamu.</p>
            </div>
        </label>
    </div>
</div>
