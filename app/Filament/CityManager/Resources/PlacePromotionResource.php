<?php

namespace App\Filament\CityManager\Resources;

use App\Filament\CityManager\Resources\PlacePromotionResource\Pages;
use App\Models\Place;
use App\Models\PlacePromotion;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PlacePromotionResource extends Resource
{
    protected static ?string $model = PlacePromotion::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-megaphone';

    protected static string | \UnitEnum | null $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Weekday Promotions';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\Select::make('place_id')
                    ->label('Place')
                    ->options(function () {
                        $cityId = auth()->user()->city_id;

                        return Place::with('translations')
                            ->where('city_id', $cityId)
                            ->get()
                            ->mapWithKeys(function ($place) {
                                $name = $place->translations->firstWhere('language_code', 'en')?->name ?? "Place #{$place->id}";
                                return [$place->id => $name];
                            });
                    })
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('day_of_week')
                    ->label('Day of Week')
                    ->options(PlacePromotion::DAY_NAMES)
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->label('Promotion Title')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g., 50% off cocktails'),
                Forms\Components\TextInput::make('discount_percent')
                    ->label('Discount %')
                    ->numeric()
                    ->nullable()
                    ->minValue(1)
                    ->maxValue(100)
                    ->suffix('%')
                    ->placeholder('Optional'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
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
                    ->getStateUsing(fn (PlacePromotion $record) => $record->place?->translations->firstWhere('language_code', 'en')?->name)
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('place.translations', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                    }),
                Tables\Columns\TextColumn::make('day_of_week')
                    ->label('Day')
                    ->formatStateUsing(fn (int $state) => PlacePromotion::DAY_NAMES[$state] ?? $state)
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Promotion')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('discount_percent')
                    ->label('Discount')
                    ->suffix('%')
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('day_of_week')
                    ->label('Day')
                    ->options(PlacePromotion::DAY_NAMES),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlacePromotions::route('/'),
            'create' => Pages\CreatePlacePromotion::route('/create'),
            'edit' => Pages\EditPlacePromotion::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('place', fn (Builder $q) => $q->where('city_id', auth()->user()->city_id));
    }
}
