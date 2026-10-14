@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Audit Trail Sistem</h1>
            <p class="text-xs text-slate-500 mt-1">Pencatatan riwayat aktivitas pengguna, pengajuan kegiatan, presensi, dan perubahan konfigurasi.</p>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-3xl p-4 md:p-6 border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('super-admin.audit-logs.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-6">
                <input type="text" 
                       name="search" 
                       value="{{ $search }}" 
                       placeholder="Cari berdasarkan nama pengguna atau detail aksi..." 
                       class="form-input text-xs py-2.5 rounded-xl">
            </div>

            <div class="sm:col-span-4">
                <select name="action" class="form-input text-xs py-2.5 rounded-xl">
                    <option value="">-- Semua Jenis Aksi --</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ $currentAction === $action ? 'selected' : '' }}>{{ $action }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 flex gap-2">
                <button type="submit" class="btn-primary w-full py-2.5 text-xs font-bold rounded-xl">Filter</button>
                @if($search || $currentAction)
                    <a href="{{ route('super-admin.audit-logs.index') }}" class="btn-secondary py-2.5 px-3 text-xs font-bold rounded-xl">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Audit Table Card -->
    <div class="bg-white rounded-2xl md:rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left text-xs min-w-[700px]">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-400 uppercase tracking-wider text-[10px]">
                        <th class="py-3.5 px-4">Waktu (WIB)</th>
                        <th class="py-3.5 px-4">Pengguna</th>
                        <th class="py-3.5 px-4">Aksi</th>
                        <th class="py-3.5 px-4">Detail Peristiwa</th>
                        <th class="py-3.5 px-4 text-right">Alamat IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                {{ $log->timestamp->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                {{ $log->username }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md font-mono text-[10px] font-bold bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-700 max-w-md">
                                {{ $log->detail }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-[11px] text-slate-400">
                                {{ $log->ip_address ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400 text-xs">
                                Tidak ada catatan audit log yang cocok dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
