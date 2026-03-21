<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlaceDiscountResource\Pages;
use App\Models\City;
use App\Models\Place;
use App\Models\PlaceDiscount;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PlaceDiscountResource extends Resource
{
    protected static ?string $model = PlaceDiscount::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-tag';

    protected static string | \UnitEnum | null $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Place Discounts';

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
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(fn (\Filament\Schemas\Components\Utilities\Set $set) => $set('place_id', null))
                    ->dehydrated(false),
                Forms\Components\Select::make('place_id')
                    ->label('Place')
                    ->options(function (\Filament\Schemas\Components\Utilities\Get $get) {
                        $cityId = $get('city_id');
                        $query = Place::with('translations');
                        if ($cityId) {
                            $query->where('city_id', $cityId);
                        }
                        return $query->get()->mapWithKeys(function ($place) {
                            $name = $place->translations->firstWhere('language_code', 'en')?->name ?? "Place #{$place->id}";
                            return [$place->id => $name];
                        });
                    })
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('discount_percent')
                    ->label('Discount %')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(100),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
                Forms\Components\DateTimePicker::make('starts_at')
                    ->label('Starts At'),
                Forms\Components\DateTimePicker::make('ends_at')
                    ->label('Ends At')
                    ->after('starts_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('place_name')
                    ->label('Place')
                    ->getStateUsing(fn (PlaceDiscount $record) => $record->place?->translations->firstWhere('language_code', 'en')?->name)
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('place.translations', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                    }),
                Tables\Columns\TextColumn::make('discount_percent')
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->sortable(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ends_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlaceDiscounts::route('/'),
            'create' => Pages\CreatePlaceDiscount::route('/create'),
            'edit' => Pages\EditPlaceDiscount::route('/{record}/edit'),
        ];
    }
}
