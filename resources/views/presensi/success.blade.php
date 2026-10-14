@extends('layouts.guest')

@section('content')
<div class="bg-white rounded-3xl p-6 md:p-8 shadow-xl border border-slate-200/80 text-center">
    <!-- Success Badge Icon -->
    <div class="w-20 h-20 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-5 shadow-lg shadow-emerald-500/20 ring-8 ring-emerald-50">
        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
        </svg>
    </div>

    <h2 class="text-2xl font-black text-slate-900 tracking-tight">Presensi Berhasil Tercatat!</h2>
    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
        Terima kasih, data kehadiran dan evaluasi Anda telah masuk ke basis data terpusat BBKK Tanjung Priok.
    </p>

    <!-- Attendance Summary Card -->
    <div class="mt-6 p-5 rounded-2xl bg-slate-50 border border-slate-200/80 text-left space-y-3">
        <div class="flex justify-between items-baseline border-b border-slate-200/60 pb-2">
            <span class="text-[11px] font-bold uppercase text-slate-400">Kegiatan</span>
            <span class="text-xs font-bold text-slate-800 text-right truncate max-w-[200px]">{{ $event->title }}</span>
        </div>

        <div class="flex justify-between items-baseline border-b border-slate-200/60 pb-2">
            <span class="text-[11px] font-bold uppercase text-slate-400">Nama Peserta</span>
            <span class="text-xs font-bold text-slate-800">{{ $attendee->full_name }}</span>
        </div>

        <div class="flex justify-between items-baseline border-b border-slate-200/60 pb-2">
            <span class="text-[11px] font-bold uppercase text-slate-400">NIP / NIK</span>
            <span class="text-xs font-mono font-bold text-slate-800">{{ $attendee->nip_or_id }}</span>
        </div>

        <div class="flex justify-between items-baseline border-b border-slate-200/60 pb-2">
            <span class="text-[11px] font-bold uppercase text-slate-400">Instansi</span>
            <span class="text-xs font-medium text-slate-700">{{ $attendee->agency }}</span>
        </div>

        <div class="flex justify-between items-baseline">
            <span class="text-[11px] font-bold uppercase text-slate-400">Waktu Tercatat</span>
            <span class="text-xs font-mono text-emerald-700 font-bold">{{ $attendee->created_at->format('d/m/Y H:i:s') }} WIB</span>
        </div>
    </div>

    <div class="mt-8">
        <a href="{{ route('presensi.show', ['token' => $event->qr_token]) }}" class="inline-flex items-center justify-center px-6 py-3 rounded-xl border border-slate-300 text-xs font-bold text-slate-600 hover:bg-slate-50 transition-colors">
            Isi Presensi Peserta Lain
        </a>
    </div>
</div>
@endsection
