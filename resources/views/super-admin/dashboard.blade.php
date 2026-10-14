@extends('layouts.app')

@section('content')
<div class="space-y-8" x-data="{
    roomModalOpen: false,
    selectedRoom: null,
    roomsData: @js($roomsData),
    openRoom(id) {
        this.selectedRoom = this.roomsData.find(r => r.id === id) || null;
        this.roomModalOpen = true;
    }
}">
    <!-- Top Greeting Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-950 rounded-3xl p-6 md:p-8 text-white shadow-xl border border-white/10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative overflow-hidden">
        <div class="space-y-2 relative z-10">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-semibold border border-emerald-500/30">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                Dasbor Super Admin
            </div>
            <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white">
                Selamat Datang, {{ auth()->user()->name }}
            </h1>
            <p class="text-xs md:text-sm text-slate-300 max-w-xl">
                Sistem Pengelolaan Presensi Digital & Monitoring Ruang Rapat Balai Besar Kekarantinaan Kesehatan Tanjung Priok.
            </p>
        </div>

        <div class="flex items-center gap-3 relative z-10">
            <a href="{{ route('super-admin.events.index') }}" class="btn-primary py-3 px-5 text-xs font-bold rounded-xl shadow-lg shadow-brand-500/30">
                Verifikasi Agenda ({{ $pendingEventsCount }})
            </a>
            <a href="{{ route('super-admin.audit-logs.index') }}" class="px-4 py-3 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold transition-colors border border-white/10">
                Lihat Log Audit
            </a>
        </div>
    </div>

    <!-- Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Metric 1: Total Agenda -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Agenda</div>
                <div class="text-3xl font-black text-slate-900 mt-1">{{ number_format($totalEvents) }}</div>
                <div class="text-[11px] text-slate-500 mt-1">{{ $approvedEventsCount }} Disetujui</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        </div>

        <!-- Metric 2: Menunggu Verifikasi -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Antrean Verifikasi</div>
                <div class="text-3xl font-black {{ $pendingEventsCount > 0 ? 'text-amber-600' : 'text-slate-900' }} mt-1">
                    {{ number_format($pendingEventsCount) }}
                </div>
                <div class="text-[11px] {{ $pendingEventsCount > 0 ? 'text-amber-600 font-semibold' : 'text-slate-500' }} mt-1">
                    {{ $pendingEventsCount > 0 ? 'Memerlukan Persetujuan' : 'Semua Bersih' }}
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl {{ $pendingEventsCount > 0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-700' }} flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- Metric 3: Total Kehadiran -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Kehadiran</div>
                <div class="text-3xl font-black text-brand-700 mt-1">{{ number_format($totalAttendees) }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Presensi Terverifikasi</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-700 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>

        <!-- Metric 4: Indeks IKM -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Kepuasan IKM</div>
                <div class="text-3xl font-black text-emerald-600 mt-1">{{ $ikmPercentage }}%</div>
                <div class="text-[11px] text-slate-500 mt-1">{{ $totalEvaluations }} Responden Survei</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>

    <!-- Main Grid: Rooms & Recent Events -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Recent Events Table (8 Cols) -->
        <div class="lg:col-span-8 bg-white rounded-3xl p-6 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 tracking-tight">Agenda Terkini</h3>
                    <p class="text-xs text-slate-500">Daftar kegiatan rapat yang terdaftar di sistem</p>
                </div>
                <a href="{{ route('super-admin.events.index') }}" class="text-xs font-bold text-brand-700 hover:text-brand-800">
                    Lihat Semua &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-400 uppercase tracking-wider text-[10px]">
                            <th class="py-3 px-3">Kegiatan</th>
                            <th class="py-3 px-3">Ruangan</th>
                            <th class="py-3 px-3">Tanggal & Waktu</th>
                            <th class="py-3 px-3">Status</th>
                            <th class="py-3 px-3 text-right">Peserta</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentEvents as $event)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-900 max-w-xs truncate">{{ $event->title }}</div>
                                    <div class="text-[10px] text-slate-500">{{ $event->work_unit }}</div>
                                </td>
                                <td class="py-3 px-3 font-medium text-slate-700">
                                    {{ $event->room->name }}
                                </td>
                                <td class="py-3 px-3">
                                    <div>{{ $event->event_date->translatedFormat('d M Y') }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $event->formatted_time }}</div>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $event->status->badgeClass() }}">
                                        {{ $event->status->label() }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right font-bold text-slate-800">
                                    {{ $event->attendees_count }} Orang
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400 text-xs">
                                    Belum ada agenda rapat yang terdaftar di sistem.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Room Availability Today (4 Cols) -->
        <div class="lg:col-span-4 bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 tracking-tight">Ketersediaan Ruang Rapat</h3>
                    <p class="text-xs text-slate-500">Status penggunaan ruang rapat dinas hari ini</p>
                </div>
                <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md shrink-0">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    {{ Carbon\Carbon::today()->translatedFormat('d M') }}
                </span>
            </div>

            <div class="space-y-2.5 pt-1">
                @foreach($rooms as $room)
                    <div role="button"
                         tabindex="0"
                         @click="openRoom('{{ $room->id }}')"
                         @keydown.enter="openRoom('{{ $room->id }}')"
                         title="Klik untuk melihat rincian agenda"
                         class="p-3.5 rounded-2xl border border-slate-200/90 bg-slate-50/70 hover:bg-white hover:border-brand-400 hover:shadow-md transition-all duration-200 flex items-center justify-between cursor-pointer group active:scale-[0.99] select-none">
                        <div class="pr-2">
                            <div class="text-xs font-bold text-slate-800 group-hover:text-brand-600 transition-colors flex items-center gap-1.5">
                                <span>{{ $room->name }}</span>
                                <svg class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all text-brand-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                            <div class="text-[10px] text-slate-500 mt-0.5 flex items-center gap-1.5">
                                <span>Kapasitas: {{ $room->capacity ?? '-' }} Kursi</span>
                                <span class="text-slate-300">•</span>
                                <span class="text-slate-400 group-hover:text-brand-600 font-medium transition-colors">Lihat agenda &rarr;</span>
                            </div>
                        </div>
                        <div class="shrink-0">
                            @if($room->events_count > 0)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 group-hover:bg-amber-100 group-hover:border-amber-300 transition-colors shadow-2xs">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    {{ $room->events_count }} Agenda
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 group-hover:bg-emerald-100 group-hover:border-emerald-300 transition-colors shadow-2xs">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Tersedia
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pt-1 flex items-center justify-center gap-1 text-[11px] text-slate-400 font-medium">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Klik salah satu ruangan untuk melihat detail agenda</span>
            </div>
        </div>
    </div>

    <!-- Modal Detail Agenda Ruang Rapat -->
    <div x-show="roomModalOpen" 
         x-cloak 
         @keydown.escape.window="roomModalOpen = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display: none;">
        <div class="bg-white rounded-3xl max-w-2xl w-full p-6 md:p-7 shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[90vh]"
             @click.away="roomModalOpen = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <!-- Modal Header -->
            <div class="flex items-start justify-between gap-4 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-brand-600 to-emerald-400 text-white flex items-center justify-center shadow-lg shadow-brand-500/20 shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-lg md:text-xl font-extrabold text-slate-900 tracking-tight" x-text="selectedRoom ? selectedRoom.name : 'Detail Ruangan'"></h2>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 mt-1 text-xs text-slate-500">
                            <span class="inline-flex items-center gap-1 font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md text-[11px]">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                <span x-text="'Kapasitas: ' + (selectedRoom?.capacity || '-') + ' Kursi'"></span>
                            </span>
                            <span class="text-slate-300">•</span>
                            <span class="text-slate-600 text-[11px] font-medium">{{ Carbon\Carbon::today()->translatedFormat('l, d F Y') }}</span>
                        </div>
                    </div>
                </div>

                <button type="button" 
                        @click="roomModalOpen = false" 
                        class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Modal Body (Scrollable) -->
            <div class="flex-1 overflow-y-auto py-4 space-y-4 pr-1">
                <!-- Case: There are agendas -->
                <template x-if="selectedRoom && selectedRoom.events && selectedRoom.events.length > 0">
                    <div class="space-y-3">
                        <div class="p-3 rounded-2xl bg-amber-50/80 border border-amber-200/80 flex items-center justify-between text-xs text-amber-900">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                <span class="font-bold">
                                    Terjadwal <span x-text="selectedRoom.events.length"></span> Agenda Rapat Hari Ini
                                </span>
                            </div>
                            <span class="text-[11px] font-medium text-amber-700">Urut berdasarkan jam</span>
                        </div>

                        <!-- Agenda Items -->
                        <div class="space-y-3">
                            <template x-for="(event, idx) in selectedRoom.events" :key="event.id">
                                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/40 hover:bg-white hover:border-brand-300 hover:shadow-sm transition-all space-y-3 relative overflow-hidden group">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                        <!-- Time pill -->
                                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-slate-900 text-white text-xs font-mono font-bold tracking-tight shadow-2xs">
                                            <svg class="w-3.5 h-3.5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span x-text="event.formatted_time"></span>
                                        </div>

                                        <!-- Status Badge -->
                                        <div>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold border"
                                                  :class="event.status_badge_class">
                                                <span class="w-1.5 h-1.5 rounded-full" :class="{
                                                    'bg-emerald-500': event.status === 'APPROVED',
                                                    'bg-amber-500': event.status === 'PENDING',
                                                    'bg-rose-500': event.status === 'REJECTED',
                                                    'bg-slate-500': event.status === 'CLOSED'
                                                }"></span>
                                                <span x-text="event.status_label"></span>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Agenda Title -->
                                    <div>
                                        <h4 class="text-sm font-bold text-slate-900 group-hover:text-brand-600 transition-colors leading-snug" x-text="event.title"></h4>
                                    </div>

                                    <!-- Event Details -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-slate-600 pt-2 border-t border-slate-100">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                            <span class="truncate font-medium text-slate-700" x-text="event.work_unit"></span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                            <span class="font-bold text-slate-800" x-text="event.attendees_count + ' Peserta Terdaftar'"></span>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                                        <template x-if="event.is_approved">
                                            <a :href="event.monitor_url" 
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 transition-colors shadow-xs">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                                <span>Buka Monitor Presensi</span>
                                            </a>
                                        </template>
                                        <template x-if="event.is_pending">
                                            <a href="{{ route('super-admin.events.index') }}" 
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-amber-800 bg-amber-100 hover:bg-amber-200 transition-colors">
                                                <svg class="w-3.5 h-3.5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span>Verifikasi Agenda</span>
                                            </a>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Case: Room is available (0 events) -->
                <template x-if="selectedRoom && (!selectedRoom.events || selectedRoom.events.length === 0)">
                    <div class="py-10 px-4 text-center space-y-3">
                        <div class="w-16 h-16 rounded-3xl bg-emerald-50 border border-emerald-200 text-emerald-600 mx-auto flex items-center justify-center shadow-xs">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-base font-extrabold text-slate-800">Ruangan Tersedia Hari Ini</h4>
                            <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1 leading-relaxed">
                                Tidak ada agenda kegiatan rapat yang dijadwalkan di ruangan ini untuk hari ini. Ruangan bebas dan siap digunakan.
                            </p>
                        </div>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold text-emerald-700 bg-emerald-100/70 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Status: Siap Digunakan
                        </div>
                    </div>
                </template>
            </div>

            <!-- Modal Footer -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('super-admin.events.index') }}" class="text-xs font-bold text-brand-700 hover:text-brand-800 flex items-center gap-1">
                    <span>Lihat Seluruh Agenda</span>
                    <span>&rarr;</span>
                </a>
                <button type="button" @click="roomModalOpen = false" class="btn-secondary py-2 px-5 text-xs font-bold rounded-xl cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
