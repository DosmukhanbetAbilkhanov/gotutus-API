<?php

namespace App\Filament\CityManager\Pages;

use App\Filament\CityManager\Widgets\CityStatsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-home';

    public function getWidgets(): array
    {
        return [
            CityStatsWidget::class,
        ];
    }
}
