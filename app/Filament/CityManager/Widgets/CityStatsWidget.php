<?php

namespace App\Filament\CityManager\Widgets;

use App\Models\Place;
use App\Models\PlaceDiscount;
use App\Models\User;
use App\Models\UserType;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CityStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $cityId = auth()->user()->city_id;

        $placesCount = Place::where('city_id', $cityId)->count();

        $activeDiscountsCount = PlaceDiscount::whereHas('place', fn ($q) => $q->where('city_id', $cityId))
            ->where('is_active', true)
            ->count();

        $clientTypeId = UserType::where('slug', UserType::SLUG_CLIENT)->value('id');
        $usersCount = User::where('city_id', $cityId)
            ->where('user_type_id', $clientTypeId)
            ->count();

        return [
            Stat::make('Places', $placesCount)
                ->description('In your city')
                ->icon('heroicon-o-map-pin'),
            Stat::make('Active Discounts', $activeDiscountsCount)
                ->description('Currently active')
                ->icon('heroicon-o-tag'),
            Stat::make('Users', $usersCount)
                ->description('Client users in your city')
                ->icon('heroicon-o-users'),
        ];
    }
}
