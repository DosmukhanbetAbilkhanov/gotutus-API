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
            'colors' => [
                'primary' => '#6366F1',
                'primaryLight' => '#EEF2FF',
                'secondary' => '#10B981',
                'backgroundLight' => '#F3F4F6',
                'backgroundDark' => '#0F172A',
                'textPrimary' => '#111827',
                'textSecondary' => '#6B7280',
                'textTertiary' => '#4B5563',
                'inputBackground' => '#F8FAFC',
                'border' => '#E5E7EB',
                'divider' => '#F1F5F9',
                'success' => '#4CAF50',
                'error' => '#E53935',
                'warning' => '#FFA726',
                'messageMine' => '#6366F1',
                'messageTheirs' => '#F1F5F9',
            ],
            'typography' => [
                'fontFamily' => 'Inter',
                'h1' => ['fontSize' => 32, 'fontWeight' => 800, 'letterSpacing' => -0.5, 'lineHeight' => 1.2],
                'h2' => ['fontSize' => 28, 'fontWeight' => 800, 'letterSpacing' => -0.3, 'lineHeight' => 1.25],
                'h3' => ['fontSize' => 22, 'fontWeight' => 700, 'lineHeight' => 1.3],
                'bodyLarge' => ['fontSize' => 16, 'fontWeight' => 400, 'lineHeight' => 1.5],
                'bodyMedium' => ['fontSize' => 14, 'fontWeight' => 400, 'lineHeight' => 1.43],
                'bodySmall' => ['fontSize' => 12, 'fontWeight' => 400, 'lineHeight' => 1.33],
                'labelLarge' => ['fontSize' => 16, 'fontWeight' => 600, 'lineHeight' => 1.5],
                'labelMedium' => ['fontSize' => 14, 'fontWeight' => 500, 'lineHeight' => 1.43],
                'labelSmall' => ['fontSize' => 12, 'fontWeight' => 500, 'lineHeight' => 1.33],
                'button' => ['fontSize' => 17, 'fontWeight' => 700, 'lineHeight' => 1.5],
                'caption' => ['fontSize' => 11, 'fontWeight' => 400, 'lineHeight' => 1.27],
            ],
            'spacing' => [
                'inputHeight' => 56,
                'buttonHeight' => 56,
                'bottomNavHeight' => 80,
                'fabSize' => 56,
                'avatarSmall' => 24,
                'avatarMedium' => 40,
                'avatarLarge' => 56,
                'inputPaddingH' => 20,
                'inputPaddingV' => 16,
                'chipPaddingH' => 12,
                'chipPaddingV' => 8,
            ],
            'border_radius' => [
                'default' => 16,
                'large' => 32,
                'xl' => 48,
                'full' => 9999,
            ],
        ];
    }
}
