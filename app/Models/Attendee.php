<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Gender;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class Attendee extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false; // Only created_at column

    protected $fillable = [
        'event_id',
        'full_name',
        'agency',
        'is_internal',
        'gender',
        'nip_or_id',
        'position',
        'phone',
        'address',
        'bank_name',
        'bank_account_no',
        'bank_account_name',
        'coordinates',
        'signature_data',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
            'gender' => Gender::class,
            'created_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function evaluation(): HasOne
    {
        return $this->hasOne(Evaluation::class);
    }

    public function hasTransport(): bool
    {
        return !empty($this->bank_account_no);
    }
}
