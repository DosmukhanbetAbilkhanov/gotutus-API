<?php

namespace App\Filament\Widgets;

use App\Enums\ReportStatus;
use App\Filament\Resources\ReportResource;
use App\Models\Report;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestReportsWidget extends BaseWidget
{
    protected static ?string $heading = 'Latest Pending Reports';

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Report::query()
                    ->where('status', ReportStatus::Pending)
                    ->with(['reporter', 'reportedUser'])
                    ->latest('created_at')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('reporter.name')
                    ->label('Reporter'),
                Tables\Columns\TextColumn::make('reportedUser.name')
                    ->label('Reported User'),
                Tables\Columns\TextColumn::make('reason')
                    ->limit(30),
                Tables\Columns\TextColumn::make('created_at')
                    ->since(),
            ])
            ->actions([
                Action::make('review')
                    ->url(fn (Report $record): string => ReportResource::getUrl('edit', ['record' => $record]))
                    ->icon('heroicon-o-eye'),
            ])
            ->paginated(false);
    }
}
