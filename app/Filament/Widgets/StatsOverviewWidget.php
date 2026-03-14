<?php

namespace App\Filament\Widgets;

use App\Enums\PhotoStatus;
use App\Enums\ReportStatus;
use App\Enums\HangoutRequestStatus;
use App\Enums\UserStatus;
use App\Models\Conversation;
use App\Models\HangoutRequest;
use App\Models\Report;
use App\Models\User;
use App\Models\UserPhoto;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->icon('heroicon-o-users')
                ->color('primary'),
            Stat::make('Active Users (Online)', User::where('is_online', true)->count())
                ->icon('heroicon-o-signal')
                ->color('success'),
            Stat::make('New Users (Today)', User::whereDate('created_at', today())->count())
                ->icon('heroicon-o-user-plus')
                ->color('info'),
            Stat::make('Pending Photos', UserPhoto::where('status', PhotoStatus::Pending)->count())
                ->icon('heroicon-o-photo')
                ->color('warning'),
            Stat::make('Pending Reports', Report::where('status', ReportStatus::Pending)->count())
                ->icon('heroicon-o-flag')
                ->color('danger'),
            Stat::make('Open Hangouts', HangoutRequest::where('status', HangoutRequestStatus::Open)->count())
                ->icon('heroicon-o-calendar')
                ->color('primary'),
            Stat::make('Active Conversations', Conversation::has('messages')->count())
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('info'),
            Stat::make('Banned Users', User::where('status', UserStatus::Banned)->count())
                ->icon('heroicon-o-no-symbol')
                ->color('danger'),
        ];
    }
}
