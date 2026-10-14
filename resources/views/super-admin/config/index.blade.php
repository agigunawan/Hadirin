@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-black tracking-tight text-slate-900">Pengaturan Institusi & Profil Pimpinan</h1>
        <p class="text-xs text-slate-500 mt-1">Konfigurasi data pimpinan penandatangan berkas SPJ dan logo institusi BBKK Tanjung Priok.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Pimpinan Form (8 Cols) -->
        <div class="lg:col-span-8 bg-white rounded-3xl p-6 md:p-8 border border-slate-200 shadow-sm">
            <h2 class="text-base font-extrabold text-slate-900 tracking-tight mb-1">Profil Pimpinan Kedinasan</h2>
            <p class="text-xs text-slate-500 mb-6">Data ini dicantumkan pada bagian akhir lembar rekapitulasi kehadiran resmi (SPJ Excel / Berita Acara).</p>

            <form method="POST" action="{{ route('super-admin.config.pimpinan') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <div>
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nama Lengkap & Gelar Pimpinan <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name', $pimpinan['name'] ?? '') }}" required class="form-input py-2.5 text-xs rounded-xl">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="nip" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            NIP Pimpinan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="nip" name="nip" value="{{ old('nip', $pimpinan['nip'] ?? '') }}" required class="form-input py-2.5 text-xs rounded-xl font-mono">
                    </div>

                    <div>
                        <label for="position" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Jabatan Kedinasan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="position" name="position" value="{{ old('position', $pimpinan['position'] ?? '') }}" required class="form-input py-2.5 text-xs rounded-xl">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Aset Tanda Tangan / Cap Pimpinan (Gambar Transparan PNG)
                    </label>
                    <input type="file" name="signature_file" accept="image/png,image/jpeg" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">

                    @if(!empty($pimpinan['signature_url']))
                        <div class="mt-3 p-3 rounded-2xl bg-slate-50 border border-slate-200 inline-block">
                            <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">Pratinjau TTD Pimpinan:</div>
                            <img src="{{ $pimpinan['signature_url'] }}" alt="TTD Pimpinan" class="h-16 object-contain">
                        </div>
                    @endif
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-primary py-3 px-6 text-xs font-bold rounded-xl shadow-lg shadow-brand-500/20">
                        Simpan Perubahan Pimpinan
                    </button>
                </div>
            </form>
        </div>

        <!-- Logo Institusi (4 Cols) -->
        <div class="lg:col-span-4 bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
            <h2 class="text-base font-extrabold text-slate-900 tracking-tight mb-1">Logo Institusi</h2>
            <p class="text-xs text-slate-500">Logo Balai Besar Kekarantinaan Kesehatan Tanjung Priok.</p>

            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col items-center justify-center min-h-[140px]">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Logo BBKK Priok" class="max-h-24 object-contain">
                @else
                    <div class="text-center text-slate-400">
                        <svg class="w-12 h-12 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <div class="text-xs font-semibold">Menggunakan Logo Bawaan Sistem</div>
                    </div>
                @endif
            </div>

            <form method="POST" action="{{ route('super-admin.config.logo') }}" enctype="multipart/form-data" class="space-y-3 pt-2">
                @csrf
                <input type="file" name="logo_file" required accept="image/*" class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                <button type="submit" class="w-full btn-secondary py-2.5 text-xs font-bold rounded-xl">
                    Perbarui Logo
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
