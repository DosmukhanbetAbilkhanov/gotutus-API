<?php

namespace App\Filament\CityManager\Resources;

use App\Filament\CityManager\Resources\PlaceResource\Pages;
use App\Models\Place;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PlaceResource extends Resource
{
    protected static ?string $model = Place::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-map-pin';

    protected static string | \UnitEnum | null $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
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
                \Filament\Schemas\Components\Section::make('Working Hours')
                    ->schema([
                        Forms\Components\Repeater::make('workingHours')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('day_of_week')
                                    ->options([
                                        0 => 'Monday',
                                        1 => 'Tuesday',
                                        2 => 'Wednesday',
                                        3 => 'Thursday',
                                        4 => 'Friday',
                                        5 => 'Saturday',
                                        6 => 'Sunday',
                                    ])
                                    ->required()
                                    ->distinct(),
                                Forms\Components\TextInput::make('open_time')
                                    ->label('Open Time (HH:MM)')
                                    ->placeholder('09:00')
                                    ->maxLength(5)
                                    ->helperText('Leave empty if closed'),
                                Forms\Components\TextInput::make('close_time')
                                    ->label('Close Time (HH:MM)')
                                    ->placeholder('22:00')
                                    ->maxLength(5)
                                    ->helperText('Leave empty if closed'),
                            ])
                            ->defaultItems(7)
                            ->minItems(7)
                            ->maxItems(7)
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
                    ->getStateUsing(fn (Place $record) => $record->translations->firstWhere('language_code', 'ru')?->name),
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
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\PlaceResource\RelationManagers\DiscountsRelationManager::class,
            \App\Filament\Resources\PlaceResource\RelationManagers\ActivityTypesRelationManager::class,
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('city_id', auth()->user()->city_id);
    }
}
