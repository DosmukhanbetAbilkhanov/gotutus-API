<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlaceRatingResource\Pages;
use App\Models\PlaceRating;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PlaceRatingResource extends Resource
{
    protected static ?string $model = PlaceRating::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-storefront';

    protected static string | \UnitEnum | null $navigationGroup = 'Feedback';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Place Ratings';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('place.name')
                    ->label('Place')
                    ->searchable()
                    ->getStateUsing(fn (PlaceRating $record) => $record->place?->translations->firstWhere('language_code', 'en')?->name ?? "Place #{$record->place_id}"),
                Tables\Columns\TextColumn::make('hangout_request_id')
                    ->label('Hangout')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Rated By')
                    ->searchable(),
                Tables\Columns\TextColumn::make('rating')
                    ->badge()
                    ->sortable()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'warning',
                        default => 'danger',
                    }),
                Tables\Columns\IconColumn::make('discount_was_active')
                    ->boolean()
                    ->label('Discount Active'),
                Tables\Columns\TextColumn::make('comment')
                    ->limit(50)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('rating')
                    ->options([1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5']),
                Tables\Filters\TernaryFilter::make('discount_was_active')
                    ->label('Discount Was Active'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlaceRatings::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
