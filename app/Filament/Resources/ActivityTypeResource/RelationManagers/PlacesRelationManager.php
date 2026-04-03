<?php

namespace App\Filament\Resources\ActivityTypeResource\RelationManagers;

use App\Models\Place;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PlacesRelationManager extends RelationManager
{
    protected static string $relationship = 'places';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->getStateUsing(fn (Place $record) => $record->translations->firstWhere('language_code', 'en')?->name),
                Tables\Columns\TextColumn::make('city_name')
                    ->label('City')
                    ->getStateUsing(fn (Place $record) => $record->city?->translations->firstWhere('language_code', 'en')?->name),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                DetachAction::make(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->recordSelect(
                        fn (Select $select) => $select
                            ->getSearchResultsUsing(function (string $search) {
                                return Place::whereHas('translations', function ($query) use ($search) {
                                    $query->where('name', 'like', "%{$search}%");
                                })
                                    ->with('translations')
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(fn (Place $place) => [
                                        $place->id => $place->translations->firstWhere('language_code', 'en')?->name ?? "Place #{$place->id}",
                                    ]);
                            })
                            ->getOptionLabelUsing(function ($value) {
                                $place = Place::with('translations')->find($value);

                                return $place?->translations->firstWhere('language_code', 'en')?->name ?? "Place #{$value}";
                            })
                    )
                    ->preloadRecordSelect(false),
            ]);
    }
}
