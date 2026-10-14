@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Edit & Pengaturan Agenda Kegiatan</h1>
            <p class="text-xs text-slate-500 mt-1">Perbarui detail agenda rapat dan atur kolom isian pada layer presensi peserta.</p>
        </div>
        <a href="{{ route('admin-tim-kerja.events.index') }}" class="btn-secondary py-2 px-4 text-xs font-bold rounded-xl">
            &larr; Kembali
        </a>
    </div>

    <div class="bg-white rounded-3xl p-6 md:p-8 border border-slate-200 shadow-sm">
        <form method="POST" action="{{ route('admin-tim-kerja.events.update', $event) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Nama Agenda -->
            <div>
                <label for="title" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Nama Kegiatan / Agenda Rapat <span class="text-rose-500">*</span>
                </label>
                <input type="text" 
                       id="title" 
                       name="title" 
                       value="{{ old('title', $event->title) }}" 
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
                        <option value="{{ $room->id }}" {{ old('room_id', $event->room_id) === $room->id ? 'selected' : '' }}>
                            {{ $room->name }} (Kapasitas: {{ $room->capacity ?? '-' }} Kursi)
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Tanggal Pelaksanaan -->
            <div>
                <label for="event_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Tanggal Pelaksanaan <span class="text-rose-500">*</span>
                </label>
                <input type="date" 
                       id="event_date" 
                       name="event_date" 
                       value="{{ old('event_date', $event->event_date->format('Y-m-d')) }}" 
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
                           value="{{ old('start_time', \App\Models\Event::minutesToTime($event->start_minutes)) }}" 
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
                           value="{{ old('end_time', \App\Models\Event::minutesToTime($event->end_minutes)) }}" 
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
                       value="{{ old('work_unit', $event->work_unit) }}" 
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
                    Simpan Perubahan & Pengaturan Kolom
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
