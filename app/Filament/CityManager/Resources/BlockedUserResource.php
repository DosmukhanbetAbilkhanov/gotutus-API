<?php

namespace App\Filament\CityManager\Resources;

use App\Filament\CityManager\Resources\BlockedUserResource\Pages;
use App\Models\BlockedUser;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BlockedUserResource extends Resource
{
    protected static ?string $model = BlockedUser::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-no-symbol';

    protected static string | \UnitEnum | null $navigationGroup = 'Users';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Blocked Users';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Blocker')
                    ->searchable(),
                Tables\Columns\TextColumn::make('blockedUser.name')
                    ->label('Blocked')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'], fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                \Filament\Actions\Action::make('unblock')
                    ->label('Unblock')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (BlockedUser $record) {
                        $record->delete();
                        Notification::make()->title('User unblocked')->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlockedUsers::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $cityId = auth()->user()->city_id;

        return parent::getEloquentQuery()
            ->where(function (Builder $q) use ($cityId) {
                $q->whereHas('user', fn ($q) => $q->where('city_id', $cityId))
                    ->orWhereHas('blockedUser', fn ($q) => $q->where('city_id', $cityId));
            })
            ->with(['user', 'blockedUser']);
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
