<div class="inline-flex items-center gap-2 cursor-pointer select-none" wire:click="toggleStatus">
    <button
        type="button"
        wire:click.stop="toggleStatus"
        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 {{ $isOpen ? 'bg-emerald-600' : 'bg-slate-300' }}"
        role="switch"
        aria-checked="{{ $isOpen ? 'true' : 'false' }}"
        title="{{ $isOpen ? 'Klik untuk menutup presensi' : 'Klik untuk membuka presensi' }}"
    >
        <span
            aria-hidden="true"
            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $isOpen ? 'translate-x-5' : 'translate-x-0' }}"
        ></span>
    </button>
    <span class="text-xs font-semibold cursor-pointer {{ $isOpen ? 'text-emerald-700' : 'text-slate-500' }}">
        {{ $isOpen ? 'Buka' : 'Tutup' }}
    </span>
</div>
