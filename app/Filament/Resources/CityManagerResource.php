<?php

namespace App\Filament\Resources;

use App\Enums\Gender;
use App\Enums\UserStatus;
use App\Filament\Resources\CityManagerResource\Pages;
use App\Models\City;
use App\Models\User;
use App\Models\UserType;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CityManagerResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-office';

    protected static string | \UnitEnum | null $navigationGroup = 'Users & Safety';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'City Managers';

    protected static ?string $modelLabel = 'City Manager';

    protected static ?string $pluralModelLabel = 'City Managers';

    protected static ?string $slug = 'city-managers';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Section::make('Manager Info')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('phone')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->maxLength(255),
                        Forms\Components\Select::make('city_id')
                            ->label('City')
                            ->options(function () {
                                return City::with('translations')->get()->mapWithKeys(function ($city) {
                                    $name = $city->translations->firstWhere('language_code', 'en')?->name ?? "City #{$city->id}";
                                    return [$city->id => $name];
                                });
                            })
                            ->required()
                            ->searchable(),
                        Forms\Components\Select::make('status')
                            ->options(collect(UserStatus::cases())->mapWithKeys(fn ($s) => [$s->value => ucfirst($s->value)]))
                            ->default('active')
                            ->required(),
                    ])
                    ->columns(2),
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
                Tables\Columns\TextColumn::make('city_name')
                    ->label('City')
                    ->getStateUsing(fn (User $record) => $record->city?->translations->firstWhere('language_code', 'en')?->name),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->color(fn ($state): string => match ($state instanceof \BackedEnum ? $state->value : $state) {
                        'active' => 'success',
                        'suspended' => 'warning',
                        'banned' => 'danger',
                        default => 'gray',
                    }),
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
                Tables\Filters\SelectFilter::make('city_id')
                    ->label('City')
                    ->options(function () {
                        return City::with('translations')->get()->mapWithKeys(function ($city) {
                            $name = $city->translations->firstWhere('language_code', 'en')?->name ?? "City #{$city->id}";
                            return [$city->id => $name];
                        });
                    }),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\Action::make('suspend')
                    ->icon('heroicon-o-pause-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (User $record) => $record->status !== UserStatus::Suspended)
                    ->action(function (User $record) {
                        $record->update(['status' => UserStatus::Suspended]);
                        $record->tokens()->delete();
                        Notification::make()->title('City manager suspended')->success()->send();
                    }),
                \Filament\Actions\Action::make('activate')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (User $record) => $record->status !== UserStatus::Active)
                    ->action(function (User $record) {
                        $record->update(['status' => UserStatus::Active]);
                        Notification::make()->title('City manager activated')->success()->send();
                    }),
                \Filament\Actions\DeleteAction::make()
                    ->before(function (User $record) {
                        $record->tokens()->delete();
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCityManagers::route('/'),
            'create' => Pages\CreateCityManager::route('/create'),
            'edit' => Pages\EditCityManager::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('userType', fn (Builder $q) => $q->where('slug', UserType::SLUG_CITY_MANAGER))
            ->with(['city.translations']);
    }
}
