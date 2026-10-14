<?php

declare(strict_types=1);

namespace App\Enums;

enum EventStatus: string
{
    case PENDING = 'PENDING';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';
    case CLOSED = 'CLOSED';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Persetujuan',
            self::APPROVED => 'Disetujui',
            self::REJECTED => 'Ditolak',
            self::CLOSED => 'Selesai / Ditutup',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'bg-amber-50 text-amber-700 border-amber-200 ring-amber-600/20',
            self::APPROVED => 'bg-emerald-50 text-emerald-700 border-emerald-200 ring-emerald-600/20',
            self::REJECTED => 'bg-rose-50 text-rose-700 border-rose-200 ring-rose-600/20',
            self::CLOSED => 'bg-slate-50 text-slate-700 border-slate-200 ring-slate-600/20',
        };
    }

    public function isJoinable(): bool
    {
        return $this === self::APPROVED;
    }
}
