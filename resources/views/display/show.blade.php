@extends('layouts.display')

@section('content')
<div class="h-screen w-screen flex flex-col justify-between p-8 md:p-12 relative overflow-hidden bg-radial from-slate-900 via-slate-950 to-black">
    <!-- Ambient Glow Effects -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-brand-500/15 rounded-full blur-3xl pointer-events-none"></div>

    <!-- Header Section -->
    <header class="flex items-center justify-between border-b border-white/10 pb-6 relative z-10">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-brand-500 to-emerald-300 text-slate-950 flex items-center justify-center font-black shadow-xl shadow-brand-500/30">
                <svg class="w-9 h-9 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white flex items-center gap-2">
                    Hadir <span class="text-brand-400">In</span>
                    <span class="text-sm font-semibold text-emerald-400 px-3 py-1 rounded-full bg-emerald-950/80 border border-emerald-500/30">Display Mode</span>
                </h1>
                <p class="text-sm text-slate-400 font-medium tracking-wide">Balai Besar Kekarantinaan Kesehatan Tanjung Priok • Kemenkes RI</p>
            </div>
        </div>

        <div class="flex items-center gap-6">
            <!-- Live Counter Component -->
            @livewire('live-counter', ['eventId' => $event->id])
        </div>
    </header>

    <!-- Main Section (Split 2 Columns 16:9) -->
    <main class="flex-1 grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-16 items-center my-auto relative z-10 py-6">
        <!-- Left: Agenda Details & 3-Step Guide (7 Cols) -->
        <div class="lg:col-span-7 space-y-8">
            <div class="space-y-4">
                <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-brand-500/20 text-brand-300 border border-brand-500/30 text-sm font-bold tracking-wide uppercase">
                    <span class="w-2.5 h-2.5 rounded-full bg-brand-400 animate-ping"></span>
                    Sesi Presensi Aktif
                </div>

                <h2 class="text-4xl md:text-5xl font-black text-white leading-tight tracking-tight drop-shadow-md">
                    {{ $event->title }}
                </h2>

                <div class="flex flex-wrap gap-4 text-base text-slate-300 pt-2">
                    <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/5 border border-white/10 backdrop-blur-sm">
                        <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span class="font-semibold">{{ $event->room->name }}</span>
                    </div>

                    <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/5 border border-white/10 backdrop-blur-sm">
                        <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>{{ $event->event_date->translatedFormat('l, d F Y') }}</span>
                    </div>

                    <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/5 border border-white/10 backdrop-blur-sm">
                        <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $event->formatted_time }}</span>
                    </div>

                    <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/5 border border-white/10 backdrop-blur-sm">
                        <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>{{ $event->work_unit }}</span>
                    </div>
                </div>
            </div>

            <!-- 3-Step Guide Cards -->
            <div class="grid grid-cols-3 gap-4 pt-4">
                <div class="bg-white/5 border border-white/10 rounded-2xl p-4 backdrop-blur-sm">
                    <div class="w-9 h-9 rounded-xl bg-brand-500/20 text-brand-300 font-bold flex items-center justify-center mb-3 text-lg border border-brand-500/30">1</div>
                    <div class="font-bold text-white text-sm mb-1">Pindai QR Code</div>
                    <div class="text-xs text-slate-400">Arahkan kamera HP ke kode QR di layar proyektor</div>
                </div>

                <div class="bg-white/5 border border-white/10 rounded-2xl p-4 backdrop-blur-sm">
                    <div class="w-9 h-9 rounded-xl bg-brand-500/20 text-brand-300 font-bold flex items-center justify-center mb-3 text-lg border border-brand-500/30">2</div>
                    <div class="font-bold text-white text-sm mb-1">Identitas & TTD</div>
                    <div class="text-xs text-slate-400">Lengkapi formulir kedinasan & bubuhkan tanda tangan</div>
                </div>

                <div class="bg-white/5 border border-white/10 rounded-2xl p-4 backdrop-blur-sm">
                    <div class="w-9 h-9 rounded-xl bg-brand-500/20 text-brand-300 font-bold flex items-center justify-center mb-3 text-lg border border-brand-500/30">3</div>
                    <div class="font-bold text-white text-sm mb-1">Survei IKM</div>
                    <div class="text-xs text-slate-400">Berikan penilaian kepuasan kegiatan secara instan</div>
                </div>
            </div>
        </div>

        <!-- Right: QR Code Visual Box (5 Cols) -->
        <div class="lg:col-span-5 flex flex-col items-center justify-center">
            <div class="relative group">
                <!-- Glowing Backlight Behind QR -->
                <div class="absolute -inset-4 bg-gradient-to-r from-brand-500 to-emerald-400 rounded-3xl blur-2xl opacity-40 group-hover:opacity-60 transition duration-1000 animate-pulse"></div>

                <!-- QR Container -->
                <div class="relative bg-white p-6 rounded-3xl shadow-2xl border-4 border-white flex flex-col items-center">
                    <div class="w-72 h-72 md:w-80 md:h-80 flex items-center justify-center [&>svg]:w-full [&>svg]:h-full">
                        {!! $qrSvg !!}
                    </div>

                    <div class="mt-4 text-center">
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-500">Tautan Manual Presensi</div>
                        <div class="text-xs font-mono font-semibold text-brand-700 select-all mt-0.5 truncate max-w-xs">
                            {{ $presensiUrl }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer Status Bar -->
    <footer class="flex items-center justify-between text-xs text-slate-400 border-t border-white/10 pt-4 relative z-10">
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
            <span>Sistem Terhubung Real-Time</span>
        </div>
        <div>
            Gunakan tombol <span class="font-mono bg-white/10 px-2 py-0.5 rounded text-white font-semibold">F11</span> pada keyboard untuk mode layar penuh proyektor
        </div>
    </footer>
</div>
@endsection
