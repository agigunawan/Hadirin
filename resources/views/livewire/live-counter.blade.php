<div wire:poll.5s class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20 text-white shadow-2xl flex items-center gap-6">
    <div class="w-16 h-16 rounded-2xl bg-brand-500/30 flex items-center justify-center border border-brand-400/30 shadow-inner">
        <svg class="w-9 h-9 text-brand-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
    </div>
    <div>
        <div class="text-xs uppercase tracking-widest text-slate-300 font-semibold mb-1">Peserta Terdaftar</div>
        <div class="text-4xl font-extrabold tracking-tight text-white flex items-baseline gap-2">
            <span>{{ number_format($count) }}</span>
            <span class="text-lg font-normal text-slate-300">Orang</span>
        </div>
    </div>
</div>
