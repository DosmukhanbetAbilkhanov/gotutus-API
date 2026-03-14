<?php

namespace App\Filament\Resources\PlaceResource\RelationManagers;

use App\Models\ActivityType;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ActivityTypesRelationManager extends RelationManager
{
    protected static string $relationship = 'activityTypes';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->getStateUsing(fn (ActivityType $record) => $record->translations->firstWhere('language_code', 'en')?->name),
                Tables\Columns\ToggleColumn::make('is_active'),
            ])
            ->defaultSort('id')
            ->headerActions([
                \Filament\Actions\AttachAction::make()
                    ->preloadRecordSelect(),
            ])
            ->actions([
                \Filament\Actions\DetachAction::make(),
            ]);
    }
}
