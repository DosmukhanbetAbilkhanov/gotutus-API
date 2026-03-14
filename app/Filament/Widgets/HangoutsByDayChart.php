<?php

namespace App\Filament\Widgets;

use App\Models\HangoutRequest;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class HangoutsByDayChart extends ChartWidget
{
    protected ?string $heading = 'Hangouts Created (Last 30 Days)';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('M d');
            $data[] = HangoutRequest::whereDate('created_at', $date)->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Hangout Requests',
                    'data' => $data,
                    'borderColor' => '#6366f1',
                    'backgroundColor' => 'rgba(99, 102, 241, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
