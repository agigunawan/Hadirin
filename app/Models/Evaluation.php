<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\IkmRating;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Evaluation extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false; // Only created_at column

    protected $fillable = [
        'attendee_id',
        'rating',
        'feedback',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'rating' => IkmRating::class,
            'created_at' => 'datetime',
        ];
    }

    public function attendee(): BelongsTo
    {
        return $this->belongsTo(Attendee::class);
    }
}
