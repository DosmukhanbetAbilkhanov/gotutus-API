<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppDesignSetting extends Model
{
    protected $fillable = [
        'colors',
        'typography',
        'spacing',
        'border_radius',
        'is_active',
        'version',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $setting) {
            $setting->version = md5(json_encode([
                $setting->colors,
                $setting->typography,
                $setting->spacing,
                $setting->border_radius,
            ]));
        });
    }

    protected function casts(): array
    {
        return [
            'colors' => 'array',
            'typography' => 'array',
            'spacing' => 'array',
            'border_radius' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public static function active(): ?self
    {
        return static::where('is_active', true)->first();
    }

    public static function defaults(): array
    {
        return [
            // Fresh Meet design system. Keys must match the Flutter
            // DesignSettings accessors (lib/core/theme/design_settings.dart).
            'colors' => [
                'primary' => '#2EA972',
                'primaryPressed' => '#218C5D',
                'primaryInk' => '#006B40',
                'primarySoft' => '#DEF7EA',
                'onPrimary' => '#FFFFFF',
                'secondary' => '#10B981',
                'background' => '#FCFBF4',
                'surface' => '#FFFFFF',
                'surfaceAlt' => '#F8F7EE',
                'backgroundDark' => '#0F172A',
                'ink' => '#1B261F',
                'inkDim' => '#58625B',
                'inkMute' => '#828A84',
                'line' => '#E1E4DE',
                'success' => '#4A9A5E',
                'successSoft' => '#DAF8DF',
                'error' => '#E53935',
                'warning' => '#FFA726',
            ],
            'typography' => [
                'fontFamily' => 'Manrope',
                'h1' => ['fontSize' => 28, 'fontWeight' => 800, 'letterSpacing' => -0.6, 'lineHeight' => 1.2],
                'h2' => ['fontSize' => 25, 'fontWeight' => 800, 'letterSpacing' => -0.75, 'lineHeight' => 1.25],
                'h3' => ['fontSize' => 19, 'fontWeight' => 700, 'letterSpacing' => -0.45, 'lineHeight' => 1.3],
                'bodyLarge' => ['fontSize' => 16, 'fontWeight' => 600, 'letterSpacing' => -0.25, 'lineHeight' => 1.5],
                'bodyMedium' => ['fontSize' => 15, 'fontWeight' => 500, 'letterSpacing' => -0.15, 'lineHeight' => 1.43],
                'bodySmall' => ['fontSize' => 13, 'fontWeight' => 600, 'letterSpacing' => 0, 'lineHeight' => 1.33],
                'labelLarge' => ['fontSize' => 16, 'fontWeight' => 700, 'letterSpacing' => -0.4, 'lineHeight' => 1.5],
                'labelMedium' => ['fontSize' => 15, 'fontWeight' => 500, 'letterSpacing' => -0.15, 'lineHeight' => 1.43],
                'labelSmall' => ['fontSize' => 13, 'fontWeight' => 600, 'letterSpacing' => 0, 'lineHeight' => 1.33],
                'button' => ['fontSize' => 17, 'fontWeight' => 700, 'letterSpacing' => -0.15, 'lineHeight' => 1.5],
                'caption' => ['fontSize' => 11, 'fontWeight' => 500, 'letterSpacing' => 0, 'lineHeight' => 1.27],
            ],
            'spacing' => [
                'inputHeight' => 56,
                'buttonHeight' => 56,
                'bottomNavHeight' => 80,
                'fabSize' => 56,
                'avatarSmall' => 24,
                'avatarMedium' => 40,
                'avatarLarge' => 56,
                'inputPaddingH' => 16,
                'inputPaddingV' => 16,
                'chipPaddingH' => 18,
                'chipPaddingV' => 12,
            ],
            'border_radius' => [
                'default' => 14,
                'large' => 18,
                'xl' => 13,
                'full' => 9999,
            ],
        ];
    }
}
