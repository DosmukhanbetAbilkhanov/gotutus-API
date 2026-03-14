<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\HangoutsByActivityChart;
use App\Filament\Widgets\HangoutsByDayChart;
use App\Filament\Widgets\LatestReportsWidget;
use App\Filament\Widgets\PendingPhotosWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\UserRegistrationChart;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-home';

    public function getWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
            HangoutsByDayChart::class,
            UserRegistrationChart::class,
            HangoutsByActivityChart::class,
            LatestReportsWidget::class,
            PendingPhotosWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 2;
    }
}
