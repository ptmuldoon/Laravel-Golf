<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null): ?string
    {
        $settings = Cache::remember('site_settings', 3600, function () {
            return static::pluck('value', 'key')->toArray();
        });

        return $settings[$key] ?? $default;
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('site_settings');
    }

    public static function getTheme(): array
    {
        $primary = static::get('theme_primary_color', '#667eea');
        $secondary = static::get('theme_secondary_color', '#764ba2');

        return [
            'primary' => $primary,
            'secondary' => $secondary,
            'primary_hover' => static::darkenColor($primary, 12),
            'primary_light' => static::lightenColor($primary, 0.955),
            'primary_rgb' => static::hexToRgb($primary),
            'secondary_rgb' => static::hexToRgb($secondary),
            'name' => static::get('theme_name', 'classic'),
        ];
    }

    public static function darkenColor(string $hex, int $percent): string
    {
        $hex = ltrim($hex, '#');
        $r = max(0, (int)(hexdec(substr($hex, 0, 2)) * (1 - $percent / 100)));
        $g = max(0, (int)(hexdec(substr($hex, 2, 2)) * (1 - $percent / 100)));
        $b = max(0, (int)(hexdec(substr($hex, 4, 2)) * (1 - $percent / 100)));

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    public static function lightenColor(string $hex, float $lightness = 0.95): string
    {
        $hex = ltrim($hex, '#');
        $r = min(255, (int)(hexdec(substr($hex, 0, 2)) + (255 - hexdec(substr($hex, 0, 2))) * $lightness));
        $g = min(255, (int)(hexdec(substr($hex, 2, 2)) + (255 - hexdec(substr($hex, 2, 2))) * $lightness));
        $b = min(255, (int)(hexdec(substr($hex, 4, 2)) + (255 - hexdec(substr($hex, 4, 2))) * $lightness));

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    public static function hexToRgb(string $hex): string
    {
        $hex = ltrim($hex, '#');

        return hexdec(substr($hex, 0, 2)) . ', ' . hexdec(substr($hex, 2, 2)) . ', ' . hexdec(substr($hex, 4, 2));
    }
}
