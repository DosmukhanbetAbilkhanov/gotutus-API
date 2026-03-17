<?php

namespace App\Filament\CityManager\Resources;

use App\Enums\Gender;
use App\Enums\UserStatus;
use App\Filament\CityManager\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\UserType;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';

    protected static string | \UnitEnum | null $navigationGroup = 'Users';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Section::make('User Info')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->disabled(),
                        Forms\Components\TextInput::make('email')
                            ->disabled(),
                        Forms\Components\TextInput::make('phone')
                            ->disabled(),
                        Forms\Components\TextInput::make('age')
                            ->disabled(),
                        Forms\Components\Select::make('gender')
                            ->options(collect(Gender::cases())->mapWithKeys(fn ($g) => [$g->value => ucfirst($g->value)]))
                            ->disabled(),
                        Forms\Components\Textarea::make('bio')
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                \Filament\Schemas\Components\Section::make('Management')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options(collect(UserStatus::cases())->mapWithKeys(fn ($s) => [$s->value => ucfirst($s->value)]))
                            ->required(),
                        Forms\Components\Placeholder::make('user_type')
                            ->label('User Type')
                            ->content(fn (User $record): string => $record->userType?->name ?? 'Client'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('age')
                    ->sortable(),
                Tables\Columns\TextColumn::make('gender')
                    ->badge()
                    ->sortable()
                    ->color(fn ($state): string => match ($state instanceof \BackedEnum ? $state->value : $state) {
                        'male' => 'info',
                        'female' => 'pink',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->color(fn ($state): string => match ($state instanceof \BackedEnum ? $state->value : $state) {
                        'active' => 'success',
                        'suspended' => 'warning',
                        'banned' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_online')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('last_seen_at')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(UserStatus::cases())->mapWithKeys(fn ($s) => [$s->value => ucfirst($s->value)])),
                Tables\Filters\SelectFilter::make('gender')
                    ->options(collect(Gender::cases())->mapWithKeys(fn ($g) => [$g->value => ucfirst($g->value)])),
                Tables\Filters\TernaryFilter::make('is_online')
                    ->label('Online'),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\Action::make('suspend')
                    ->icon('heroicon-o-pause-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (User $record) => $record->status !== UserStatus::Suspended)
                    ->action(function (User $record) {
                        $record->update(['status' => UserStatus::Suspended]);
                        $record->tokens()->delete();
                        Notification::make()->title('User suspended and tokens revoked')->success()->send();
                    }),
                \Filament\Actions\Action::make('ban')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (User $record) => $record->status !== UserStatus::Banned)
                    ->action(function (User $record) {
                        $record->update(['status' => UserStatus::Banned]);
                        $record->tokens()->delete();
                        Notification::make()->title('User banned and tokens revoked')->success()->send();
                    }),
                \Filament\Actions\Action::make('activate')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (User $record) => $record->status !== UserStatus::Active)
                    ->action(function (User $record) {
                        $record->update(['status' => UserStatus::Active]);
                        Notification::make()->title('User activated')->success()->send();
                    }),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\BulkAction::make('ban_selected')
                        ->label('Ban Selected')
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each(function (User $record) {
                                $record->update(['status' => UserStatus::Banned]);
                                $record->tokens()->delete();
                            });
                            Notification::make()->title('Selected users banned')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    \Filament\Actions\BulkAction::make('suspend_selected')
                        ->label('Suspend Selected')
                        ->icon('heroicon-o-pause-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each(function (User $record) {
                                $record->update(['status' => UserStatus::Suspended]);
                                $record->tokens()->delete();
                            });
                            Notification::make()->title('Selected users suspended')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    \Filament\Actions\BulkAction::make('activate_selected')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each(fn (User $record) => $record->update(['status' => UserStatus::Active]));
                            Notification::make()->title('Selected users activated')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'view' => Pages\ViewUser::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $clientTypeId = UserType::where('slug', UserType::SLUG_CLIENT)->value('id');

        return parent::getEloquentQuery()
            ->where('city_id', auth()->user()->city_id)
            ->where('user_type_id', $clientTypeId);
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
