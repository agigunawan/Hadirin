@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ signatureModal: false, activeSignature: null, activeAttendeeName: '' }">
    <!-- Header with Event Context -->
    <div class="bg-white rounded-3xl p-6 md:p-8 border border-slate-200 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold border mb-2 {{ $event->status->badgeClass() }}">
                {{ $event->status->label() }}
            </div>
            <h1 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight">{{ $event->title }}</h1>
            <p class="text-xs text-slate-500 mt-1">
                {{ $event->room->name }} • {{ $event->event_date->translatedFormat('l, d F Y') }} • {{ $event->formatted_time }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin-tim-kerja.events.export', $event) }}" class="btn-primary py-2.5 px-4 text-xs font-bold rounded-xl shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Unduh SPJ Excel</span>
            </a>

            <a href="{{ route('admin-tim-kerja.events.export-pdf', $event) }}" class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-sm transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-6 4h4"/></svg>
                <span>Unduh SPJ PDF</span>
            </a>

            <a href="{{ route('display.show', ['token' => $event->qr_token]) }}" target="_blank" class="btn-secondary py-2.5 px-4 text-xs font-bold rounded-xl flex items-center gap-1.5">
                <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                <span>Layar Proyektor</span>
            </a>
        </div>
    </div>

    <!-- Attendees Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-extrabold text-slate-900 tracking-tight">Daftar Hadir Terverifikasi</h3>
                <p class="text-xs text-slate-500">Total {{ $attendees->total() }} orang telah mengisi presensi</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-400 uppercase tracking-wider text-[10px]">
                        <th class="py-3.5 px-4">Nama Lengkap & NIP</th>
                        <th class="py-3.5 px-4">Instansi & Jabatan</th>
                        <th class="py-3.5 px-4">Status & Kontak</th>
                        <th class="py-3.5 px-4">Transportasi</th>
                        <th class="py-3.5 px-4">Tanda Tangan</th>
                        <th class="py-3.5 px-4 text-right">Waktu Presensi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendees as $attendee)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ $attendee->full_name }}</div>
                                @if($attendee->nip_or_id)
                                    <div class="font-mono text-[11px] text-slate-500 mt-0.5">NIP: {{ $attendee->nip_or_id }}</div>
                                @endif
                            </td>

                            <td class="py-3.5 px-4">
                                <div class="font-medium text-slate-800">{{ $attendee->agency ?? '-' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $attendee->position ?? '-' }}</div>
                            </td>

                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $attendee->is_internal ? 'bg-emerald-50 text-emerald-700' : 'bg-blue-50 text-blue-700' }}">
                                    {{ $attendee->is_internal ? 'Internal' : 'Eksternal' }}
                                </span>
                                @if($attendee->phone)
                                    <div class="text-[11px] text-slate-500 font-mono mt-0.5">{{ $attendee->phone }}</div>
                                @endif
                            </td>

                            <td class="py-3.5 px-4">
                                @if($attendee->hasTransport())
                                    <div class="text-[11px] font-bold text-slate-800">{{ $attendee->bank_name }}</div>
                                    <div class="text-[10px] font-mono text-slate-500">{{ $attendee->bank_account_no }}</div>
                                    <div class="text-[10px] text-slate-400">a.n {{ $attendee->bank_account_name }}</div>
                                @else
                                    <span class="text-[11px] text-slate-400">Tanpa Transport</span>
                                @endif
                            </td>

                            <td class="py-3.5 px-4">
                                <button type="button" 
                                        @click="signatureModal = true; activeSignature = '{{ $attendee->signature_data }}'; activeAttendeeName = '{{ addslashes($attendee->full_name) }}'"
                                        class="p-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 transition-colors shadow-2xs">
                                    <img src="{{ $attendee->signature_data }}" alt="TTD" class="h-7 w-16 object-contain">
                                </button>
                            </td>

                            <td class="py-3.5 px-4 text-right font-mono text-[11px] text-slate-500">
                                {{ $attendee->created_at->format('H:i:s') }} WIB
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400 text-xs">
                                Belum ada peserta yang mengisi daftar hadir kegiatan ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $attendees->links() }}
        </div>
    </div>

    <!-- Signature Preview Modal -->
    <div x-show="signatureModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         style="display: none;">
        <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-slate-200 text-center" @click.away="signatureModal = false">
            <h3 class="text-sm font-extrabold text-slate-900">Tanda Tangan Digital</h3>
            <p class="text-xs text-slate-500 mt-0.5" x-text="activeAttendeeName"></p>

            <div class="my-4 p-4 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-center">
                <img :src="activeSignature" alt="Tanda Tangan" class="max-h-40 object-contain">
            </div>

            <button type="button" @click="signatureModal = false" class="btn-secondary py-2 px-6 text-xs font-bold rounded-xl">
                Tutup
            </button>
        </div>
    </div>
</div>
@endsection
