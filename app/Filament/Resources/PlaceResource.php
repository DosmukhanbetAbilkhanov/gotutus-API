<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlaceResource\Pages;
use App\Filament\Resources\PlaceResource\RelationManagers;
use App\Models\ActivityType;
use App\Models\City;
use App\Models\Place;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PlaceResource extends Resource
{
    protected static ?string $model = Place::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-map-pin';

    protected static string | \UnitEnum | null $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
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
                Forms\Components\CheckboxList::make('activityTypes')
                    ->relationship('activityTypes', 'slug')
                    ->label('Activity Types')
                    ->columns(2),
                \Filament\Schemas\Components\Section::make('Translations')
                    ->schema([
                        Forms\Components\Repeater::make('translations')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('language_code')
                                    ->options([
                                        'en' => 'English',
                                        'ru' => 'Russian',
                                        'kk' => 'Kazakh',
                                    ])
                                    ->required()
                                    ->distinct(),
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('address')
                                    ->maxLength(255),
                            ])
                            ->defaultItems(3)
                            ->minItems(3)
                            ->maxItems(3)
                            ->reorderable(false)
                            ->addable(false)
                            ->deletable(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name_en')
                    ->label('Name (EN)')
                    ->getStateUsing(fn (Place $record) => $record->translations->firstWhere('language_code', 'en')?->name)
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('translations', fn ($q) => $q->where('language_code', 'en')->where('name', 'like', "%{$search}%"));
                    }),
                Tables\Columns\TextColumn::make('name_ru')
                    ->label('Name (RU)')
                    ->getStateUsing(fn (Place $record) => $record->translations->firstWhere('language_code', 'ru')?->name)
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('translations', fn ($q) => $q->where('language_code', 'ru')->where('name', 'like', "%{$search}%"));
                    }),
                Tables\Columns\TextColumn::make('address_en')
                    ->label('Address (EN)')
                    ->getStateUsing(fn (Place $record) => $record->translations->firstWhere('language_code', 'en')?->address)
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('translations', fn ($q) => $q->where('language_code', 'en')->where('address', 'like', "%{$search}%"));
                    }),
                Tables\Columns\TextColumn::make('city_name')
                    ->label('City')
                    ->getStateUsing(fn (Place $record) => $record->city?->translations->firstWhere('language_code', 'en')?->name),
                Tables\Columns\TextColumn::make('activity_types_list')
                    ->label('Activity Types')
                    ->getStateUsing(fn (Place $record) => $record->activityTypes->pluck('slug')->join(', ')),
                Tables\Columns\TextColumn::make('discount')
                    ->label('Active Discount')
                    ->getStateUsing(fn (Place $record) => $record->activeDiscount?->discount_percent ? "{$record->activeDiscount->discount_percent}%" : 'None'),
                Tables\Columns\TextColumn::make('hangout_requests_count')
                    ->counts('hangoutRequests')
                    ->label('Hangouts')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id')
            ->filters([
                Tables\Filters\SelectFilter::make('city_id')
                    ->label('City')
                    ->options(function () {
                        return City::with('translations')->get()->mapWithKeys(function ($city) {
                            $name = $city->translations->firstWhere('language_code', 'en')?->name ?? "City #{$city->id}";
                            return [$city->id => $name];
                        });
                    }),
                Tables\Filters\TernaryFilter::make('has_discount')
                    ->label('Has Active Discount')
                    ->queries(
                        true: fn ($query) => $query->whereHas('activeDiscount'),
                        false: fn ($query) => $query->whereDoesntHave('activeDiscount'),
                    ),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DiscountsRelationManager::class,
            RelationManagers\ActivityTypesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlaces::route('/'),
            'create' => Pages\CreatePlace::route('/create'),
            'edit' => Pages\EditPlace::route('/{record}/edit'),
        ];
    }
}
