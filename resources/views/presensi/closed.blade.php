@extends('layouts.guest')

@section('content')
<div class="bg-white rounded-3xl p-6 md:p-8 shadow-xl border border-slate-200/80 text-center">
    <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-500 flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
    </div>

    <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Sesi Presensi Ditutup</h2>
    <p class="text-xs text-slate-500 mt-2 max-w-sm mx-auto">
        {{ $message ?? 'Sesi daftar hadir untuk kegiatan ini sedang tidak aktif atau telah selesai.' }}
    </p>

    @if(isset($event))
        <div class="mt-6 p-4 rounded-2xl bg-slate-50 border border-slate-200 text-left">
            <div class="text-xs font-bold text-slate-800">{{ $event->title }}</div>
            <div class="text-[11px] text-slate-500 mt-1">{{ $event->event_date->translatedFormat('l, d F Y') }} • {{ $event->formatted_time }}</div>
        </div>
    @endif
</div>
@endsection
