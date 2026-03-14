<?php

namespace App\Filament\Widgets;

use App\Models\ActivityType;
use App\Models\HangoutRequest;
use Filament\Widgets\ChartWidget;

class HangoutsByActivityChart extends ChartWidget
{
    protected ?string $heading = 'Hangouts by Activity Type';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $activityTypes = ActivityType::withCount('hangoutRequests')
            ->with('translations')
            ->orderByDesc('hangout_requests_count')
            ->limit(10)
            ->get();

        $labels = $activityTypes->map(fn ($type) => $type->name ?? $type->slug)->toArray();
        $data = $activityTypes->pluck('hangout_requests_count')->toArray();

        $colors = [
            '#6366f1', '#8b5cf6', '#a855f7', '#d946ef', '#ec4899',
            '#f43f5e', '#ef4444', '#f97316', '#eab308', '#22c55e',
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Hangouts',
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
