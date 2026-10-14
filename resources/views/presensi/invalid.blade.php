@extends('layouts.guest')

@section('content')
<div class="bg-white rounded-3xl p-6 md:p-8 shadow-xl border border-slate-200/80 text-center">
    <div class="w-16 h-16 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    </div>

    <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Tautan Tidak Valid</h2>
    <p class="text-xs text-slate-500 mt-2 max-w-sm mx-auto">
        {{ $message ?? 'Kode QR atau tautan presensi yang Anda akses tidak terdaftar di sistem.' }}
    </p>

    <div class="mt-6">
        <a href="{{ route('login') }}" class="btn-primary py-2.5 px-6 text-xs font-bold rounded-xl">
            Menuju Halaman Login
        </a>
    </div>
</div>
@endsection
