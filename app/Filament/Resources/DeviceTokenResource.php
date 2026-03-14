<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeviceTokenResource\Pages;
use App\Models\DeviceToken;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DeviceTokenResource extends Resource
{
    protected static ?string $model = DeviceToken::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-device-phone-mobile';

    protected static string | \UnitEnum | null $navigationGroup = 'System';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Device Tokens';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),
                Tables\Columns\TextColumn::make('platform')
                    ->badge()
                    ->sortable()
                    ->color(fn ($state): string => match ($state instanceof \BackedEnum ? $state->value : $state) {
                        'ios' => 'info',
                        'android' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('token')
                    ->limit(30)
                    ->copyable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('platform')
                    ->options([
                        'ios' => 'iOS',
                        'android' => 'Android',
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeviceTokens::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
