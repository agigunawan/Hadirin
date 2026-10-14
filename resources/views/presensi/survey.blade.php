@extends('layouts.guest')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-3xl p-6 md:p-8 shadow-xl border border-slate-200/80 text-center">
        <div class="w-16 h-16 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center mx-auto mb-4 shadow-md shadow-amber-500/20">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
        </div>

        <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Survei Kepuasan Pelayanan (IKM)</h2>
        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
            Terima kasih Bapak/Ibu <span class="font-bold text-slate-700">{{ $attendee->full_name }}</span>. Mohon luangkan 10 detik untuk menilai kualitas pelaksanaan kegiatan ini.
        </p>

        <form method="POST" action="{{ route('presensi.survey.store', ['token' => $token, 'attendee' => $attendee->id]) }}" class="mt-8 space-y-6 text-left" x-data="{ selectedRating: '{{ old('rating', 'SANGAT_BAIK') }}' }">
            @csrf

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-3 text-center">
                    Bagaimana Tingkat Kepuasan Anda? <span class="text-rose-500">*</span>
                </label>

                <div class="grid grid-cols-2 gap-3">
                    <button type="button" 
                            @click="selectedRating = 'SANGAT_BAIK'"
                            :class="selectedRating === 'SANGAT_BAIK' ? 'border-amber-500 bg-amber-50/80 ring-2 ring-amber-500/30' : 'border-slate-200 bg-white hover:bg-slate-50'"
                            class="p-4 rounded-2xl border text-center transition-all group">
                        <div class="text-3xl mb-1 group-hover:scale-110 transition-transform">🌟</div>
                        <div class="text-xs font-extrabold text-slate-800">Sangat Baik</div>
                        <div class="text-[10px] text-slate-400">Sangat memuaskan</div>
                    </button>

                    <button type="button" 
                            @click="selectedRating = 'BAIK'"
                            :class="selectedRating === 'BAIK' ? 'border-emerald-500 bg-emerald-50/80 ring-2 ring-emerald-500/30' : 'border-slate-200 bg-white hover:bg-slate-50'"
                            class="p-4 rounded-2xl border text-center transition-all group">
                        <div class="text-3xl mb-1 group-hover:scale-110 transition-transform">👍</div>
                        <div class="text-xs font-extrabold text-slate-800">Baik</div>
                        <div class="text-[10px] text-slate-400">Sesuai ekspektasi</div>
                    </button>

                    <button type="button" 
                            @click="selectedRating = 'CUKUP'"
                            :class="selectedRating === 'CUKUP' ? 'border-sky-500 bg-sky-50/80 ring-2 ring-sky-500/30' : 'border-slate-200 bg-white hover:bg-slate-50'"
                            class="p-4 rounded-2xl border text-center transition-all group">
                        <div class="text-3xl mb-1 group-hover:scale-110 transition-transform">😐</div>
                        <div class="text-xs font-extrabold text-slate-800">Cukup</div>
                        <div class="text-[10px] text-slate-400">Perlu perbaikan</div>
                    </button>

                    <button type="button" 
                            @click="selectedRating = 'KURANG'"
                            :class="selectedRating === 'KURANG' ? 'border-rose-500 bg-rose-50/80 ring-2 ring-rose-500/30' : 'border-slate-200 bg-white hover:bg-slate-50'"
                            class="p-4 rounded-2xl border text-center transition-all group">
                        <div class="text-3xl mb-1 group-hover:scale-110 transition-transform">👎</div>
                        <div class="text-xs font-extrabold text-slate-800">Kurang</div>
                        <div class="text-[10px] text-slate-400">Tidak memuaskan</div>
                    </button>
                </div>

                <input type="hidden" name="rating" :value="selectedRating">
            </div>

            <div>
                <label for="feedback" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Kritik, Saran & Masukan (Opsional)
                </label>
                <textarea id="feedback" 
                          name="feedback" 
                          rows="3" 
                          placeholder="Tuliskan masukan untuk peningkatan kualitas acara selanjutnya..." 
                          class="form-input text-xs rounded-2xl py-3"></textarea>
            </div>

            <button type="submit" class="w-full btn-primary py-4 text-base font-bold rounded-2xl shadow-xl shadow-brand-600/30">
                Simpan Evaluasi
            </button>
        </form>
    </div>
</div>
@endsection
