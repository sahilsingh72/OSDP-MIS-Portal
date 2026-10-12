<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'description',
        'group',
    ];

    /**
     * Get setting value by key with fallback
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set setting value by key
     */
    public static function set(string $key, mixed $value, ?string $description = null, string $group = 'general'): self
    {
        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => (string)$value,
                'description' => $description,
                'group' => $group,
            ]
        );
    }
}
