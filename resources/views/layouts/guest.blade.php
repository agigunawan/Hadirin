<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">

    <title>{{ $title ?? 'Daftar Hadir Online' }} — Hadir In BBKK Tanjung Priok</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full font-sans antialiased bg-gradient-to-br from-slate-100 via-slate-50 to-emerald-50/40 text-slate-800 flex flex-col justify-between">
    <!-- Top Header Ribbon -->
    <header class="bg-white/80 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-30 shadow-xs">
        <div class="max-w-xl mx-auto px-4 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-emerald-400 flex items-center justify-center text-white font-black shadow-md shadow-brand-500/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-base font-bold text-slate-900 leading-tight flex items-center gap-1">
                        Hadir <span class="text-brand-600">In</span>
                    </h1>
                    <p class="text-[11px] text-slate-500 font-medium">BBKK Tanjung Priok • Kemenkes RI</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    E-Presensi
                </span>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-xl w-full mx-auto p-4 md:py-8">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="py-6 text-center text-xs text-slate-400 border-t border-slate-200/60 bg-white/40">
        <div class="max-w-xl mx-auto px-4">
            <p class="font-medium text-slate-500">Balai Besar Kekarantinaan Kesehatan Tanjung Priok</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Sistem Daftar Hadir Digital Berbasis QR Code & E-Signature © 2026</p>
        </div>
    </footer>
</body>
</html>
