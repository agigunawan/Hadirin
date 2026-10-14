@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Pengajuan Agenda Rapat Baru</h1>
            <p class="text-xs text-slate-500 mt-1">Sesuai regulasi FR-01, pengajuan agenda wajib dilakukan minimal H-1 sebelum pelaksanaan.</p>
        </div>
        <a href="{{ route('admin-tim-kerja.events.index') }}" class="btn-secondary py-2 px-4 text-xs font-bold rounded-xl">
            &larr; Kembali
        </a>
    </div>

    <div class="bg-white rounded-3xl p-6 md:p-8 border border-slate-200 shadow-sm">
        <form method="POST" action="{{ route('admin-tim-kerja.events.store') }}" class="space-y-5">
            @csrf

            <!-- Nama Agenda -->
            <div>
                <label for="title" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Nama Kegiatan / Agenda Rapat <span class="text-rose-500">*</span>
                </label>
                <input type="text" 
                       id="title" 
                       name="title" 
                       value="{{ old('title') }}" 
                       required 
                       placeholder="Contoh: Rapat Koordinasi Kekarantinaan Kapal Penumpang Pelabuhan Tanjung Priok" 
                       class="form-input py-2.5 text-xs rounded-xl">
            </div>

            <!-- Ruangan Rapat -->
            <div>
                <label for="room_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Pilih Ruang Rapat <span class="text-rose-500">*</span>
                </label>
                <select id="room_id" name="room_id" required class="form-input py-2.5 text-xs rounded-xl">
                    <option value="">-- Pilih Ruang Rapat Dinas --</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" {{ old('room_id') === $room->id ? 'selected' : '' }}>
                            {{ $room->name }} (Kapasitas: {{ $room->capacity ?? '-' }} Kursi)
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Tanggal Pelaksanaan (Enforcing H-1) -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="event_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Tanggal Pelaksanaan <span class="text-rose-500">*</span>
                    </label>
                    <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                        Minimal H-1 (Mulai Besok)
                    </span>
                </div>
                <input type="date" 
                       id="event_date" 
                       name="event_date" 
                       min="{{ \Carbon\Carbon::tomorrow()->format('Y-m-d') }}" 
                       value="{{ old('event_date', \Carbon\Carbon::tomorrow()->format('Y-m-d')) }}" 
                       required 
                       class="form-input py-2.5 text-xs rounded-xl font-mono">
            </div>

            <!-- Jam Pelaksanaan -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="start_time" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Jam Mulai <span class="text-rose-500">*</span>
                    </label>
                    <input type="time" 
                           id="start_time" 
                           name="start_time" 
                           value="{{ old('start_time', '09:00') }}" 
                           required 
                           class="form-input py-2.5 text-xs rounded-xl font-mono">
                </div>

                <div>
                    <label for="end_time" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Jam Selesai <span class="text-rose-500">*</span>
                    </label>
                    <input type="time" 
                           id="end_time" 
                           name="end_time" 
                           value="{{ old('end_time', '12:00') }}" 
                           required 
                           class="form-input py-2.5 text-xs rounded-xl font-mono">
                </div>
            </div>

            <!-- Tim Kerja Penyelenggara -->
            <div>
                <label for="work_unit" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Tim Kerja / Bagian Penyelenggara <span class="text-rose-500">*</span>
                </label>
                <input type="text" 
                       id="work_unit" 
                       name="work_unit" 
                       value="{{ old('work_unit', auth()->user()->work_unit ?? 'Tim Kerja BBKK Priok') }}" 
                       required 
                       class="form-input py-2.5 text-xs rounded-xl">
            </div>

            <!-- Konfigurasi Kolom Form Presensi Tamu (Layer Peserta) -->
            @include('admin-tim-kerja.events._config_fields')

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('admin-tim-kerja.events.index') }}" class="btn-secondary py-3 px-5 text-xs font-bold rounded-xl">
                    Batal
                </a>
                <button type="submit" class="btn-primary py-3 px-6 text-xs font-bold rounded-xl shadow-lg shadow-brand-500/25">
                    Kirim Permohonan Agenda
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
