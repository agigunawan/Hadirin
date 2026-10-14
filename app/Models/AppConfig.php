<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class AppConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'config_key',
        'config_value',
    ];

    protected function casts(): array
    {
        return [
            'config_value' => 'array',
        ];
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $config = self::where('config_key', $key)->first();
        return $config ? $config->config_value : $default;
    }

    public static function setValue(string $key, array $value): self
    {
        return self::updateOrCreate(
            ['config_key' => $key],
            ['config_value' => $value]
        );
    }
}
