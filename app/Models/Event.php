<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EventStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Event extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'room_id',
        'event_date',
        'start_minutes',
        'end_minutes',
        'work_unit',
        'status',
        'rejection_note',
        'qr_token',
        'config_fields',
        'pimpinan_name',
        'pimpinan_nip',
        'pimpinan_position',
        'pimpinan_date_text',
        'pimpinan_signature',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'start_minutes' => 'integer',
            'end_minutes' => 'integer',
            'status' => EventStatus::class,
            'config_fields' => 'array',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(Attendee::class);
    }

    public const DEFAULT_FIELDS_CONFIG = [
        'full_name' => ['enabled' => true, 'required' => true],
        'nip_or_id' => ['enabled' => true, 'required' => true],
        'gender' => ['enabled' => true, 'required' => true],
        'agency' => ['enabled' => true, 'required' => true],
        'position' => ['enabled' => true, 'required' => true],
        'phone' => ['enabled' => true, 'required' => true],
        'address' => ['enabled' => true, 'required' => true],
        'coordinates' => ['enabled' => false, 'required' => false],
        'transport' => ['enabled' => false, 'required' => false],
    ];

    public function getResolvedConfigFields(): array
    {
        if (empty($this->config_fields)) {
            return self::DEFAULT_FIELDS_CONFIG;
        }

        $fields = $this->config_fields['fields'] ?? $this->config_fields;

        if (array_is_list($fields)) {
            $resolved = [];
            foreach (self::DEFAULT_FIELDS_CONFIG as $key => $default) {
                $enabled = in_array($key, $fields, true);
                $resolved[$key] = [
                    'enabled' => $enabled,
                    'required' => $enabled,
                ];
            }
            return $resolved;
        }

        $resolved = [];
        foreach (self::DEFAULT_FIELDS_CONFIG as $key => $default) {
            $isEnabled = false;
            if (isset($fields[$key])) {
                $item = $fields[$key];
                if (is_array($item)) {
                    $isEnabled = filter_var($item['enabled'] ?? $item['required'] ?? false, FILTER_VALIDATE_BOOLEAN);
                } else {
                    $isEnabled = filter_var($item, FILTER_VALIDATE_BOOLEAN);
                }
            }

            $resolved[$key] = [
                'enabled' => $isEnabled,
                'required' => $isEnabled,
            ];
        }

        return $resolved;
    }

    public function isFieldActive(string $field): bool
    {
        $configs = $this->getResolvedConfigFields();
        return (bool) ($configs[$field]['enabled'] ?? false);
    }

    public function isFieldRequired(string $field): bool
    {
        $configs = $this->getResolvedConfigFields();
        return (bool) ($configs[$field]['required'] ?? false);
    }

    public function isJoinable(): bool
    {
        return $this->status === EventStatus::APPROVED;
    }

    public static function minutesToTime(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;
        return sprintf('%02d:%02d', $hours, $mins);
    }

    public static function timeToMinutes(string $timeString): int
    {
        $parts = explode(':', $timeString);
        return ((int) ($parts[0] ?? 0) * 60) + (int) ($parts[1] ?? 0);
    }

    protected function formattedTime(): Attribute
    {
        return Attribute::make(
            get: fn () => self::minutesToTime($this->start_minutes) . ' - ' . self::minutesToTime($this->end_minutes) . ' WIB'
        );
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', EventStatus::APPROVED);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', EventStatus::PENDING);
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('event_date', Carbon::today());
    }
}
