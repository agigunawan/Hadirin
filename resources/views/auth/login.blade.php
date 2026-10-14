@extends('layouts.guest')

@section('content')
<div class="bg-white rounded-3xl p-6 md:p-8 shadow-xl border border-slate-200/80"
     x-data="{ 
         username: '{{ old('username', '') }}', 
         password: '', 
         showPassword: false,
         fill(u, p) { 
             this.username = u; 
             this.password = p; 
         } 
     }">
    <div class="text-center mb-8">
        <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-brand-600 to-emerald-400 text-white flex items-center justify-center mx-auto mb-4 shadow-lg shadow-brand-500/25">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>
        <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Masuk ke Sistem</h2>
        <p class="text-sm text-slate-500 mt-1.5">Portal Pengelola & Admin Tim Kerja Hadir In</p>
    </div>

    @if($errors->any())
        <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-start gap-3">
            <svg class="w-5 h-5 text-rose-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                @foreach($errors->all() as $error)
                    <p class="font-medium">{{ $error }}</p>
                @endforeach
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('login.attempt') }}" class="space-y-5">
        @csrf

        <div>
            <label for="username" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Nama Pengguna (Username)</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7 7z"/></svg>
                </div>
                <input type="text" 
                       id="username" 
                       name="username" 
                       x-model="username" 
                       required 
                       autofocus 
                       placeholder="Contoh: superadmin atau timker1"
                       class="form-input pl-11 py-3 text-sm rounded-xl">
            </div>
        </div>

        <div>
            <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Kata Sandi</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <input :type="showPassword ? 'text' : 'password'" 
                       id="password" 
                       name="password" 
                       x-model="password" 
                       required 
                       placeholder="••••••••••••"
                       class="form-input pl-11 pr-11 py-3 text-sm rounded-xl">
                <button type="button" 
                        @click="showPassword = !showPassword" 
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-hidden"
                        tabindex="-1">
                    <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="flex items-center justify-between text-sm">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="remember" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                <span class="text-slate-600 text-xs font-medium">Ingat saya di perangkat ini</span>
            </label>
        </div>

        <button type="submit" class="w-full btn-primary py-3.5 text-base font-bold rounded-2xl shadow-lg shadow-brand-600/30">
            Masuk ke Dasbor
        </button>
    </form>

    <!-- Quick Credentials Helper for Development -->
    <div class="mt-8 pt-6 border-t border-slate-100 bg-slate-50/70 -mx-6 -mb-6 p-6 rounded-b-3xl">
        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-3 text-center">Klik untuk Isi Cepat (Pilih Akun):</div>
        <div class="grid grid-cols-2 gap-2 text-xs">
            <button type="button" 
                    @click="fill('superadmin', 'AdminPriok2026!')" 
                    class="text-left p-3 rounded-xl bg-white border border-slate-200 hover:border-brand-500 hover:bg-brand-50/40 transition-all cursor-pointer shadow-2xs group focus:outline-hidden focus:ring-2 focus:ring-brand-500">
                <div class="flex items-center justify-between mb-1">
                    <span class="font-bold text-slate-800 group-hover:text-brand-700">Super Admin</span>
                    <span class="text-[9px] font-bold uppercase tracking-wider text-brand-700 bg-brand-50 group-hover:bg-brand-100 px-1.5 py-0.5 rounded">Isi</span>
                </div>
                <div class="text-slate-500 font-mono text-[11px]">User: <strong class="text-slate-700">superadmin</strong></div>
                <div class="text-slate-500 font-mono text-[11px]">Pass: <strong class="text-slate-700">AdminPriok2026!</strong></div>
            </button>

            <button type="button" 
                    @click="fill('timker1', 'TimkerPriok2026!')" 
                    class="text-left p-3 rounded-xl bg-white border border-slate-200 hover:border-brand-500 hover:bg-brand-50/40 transition-all cursor-pointer shadow-2xs group focus:outline-hidden focus:ring-2 focus:ring-brand-500">
                <div class="flex items-center justify-between mb-1">
                    <span class="font-bold text-slate-800 group-hover:text-brand-700">Admin Tim Kerja</span>
                    <span class="text-[9px] font-bold uppercase tracking-wider text-brand-700 bg-brand-50 group-hover:bg-brand-100 px-1.5 py-0.5 rounded">Isi</span>
                </div>
                <div class="text-slate-500 font-mono text-[11px]">User: <strong class="text-slate-700">timker1</strong></div>
                <div class="text-slate-500 font-mono text-[11px]">Pass: <strong class="text-slate-700">TimkerPriok2026!</strong></div>
            </button>
        </div>
    </div>
</div>
@endsection
