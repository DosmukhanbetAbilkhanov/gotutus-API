<?php

namespace App\Filament\Resources\HangoutRequestResource\RelationManagers;

use App\Models\JoinRequest;
use App\Models\Place;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class JoinRequestsRelationManager extends RelationManager
{
    protected static string $relationship = 'joinRequests';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),
                Tables\Columns\TextColumn::make('suggested_place')
                    ->label('Suggested Place')
                    ->getStateUsing(fn (JoinRequest $record) => $record->suggestedPlace?->translations->firstWhere('language_code', 'en')?->name),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state): string => match ($state instanceof \BackedEnum ? $state->value : $state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'confirmed' => 'primary',
                        'declined' => 'danger',
                        'cancelled' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('message')
                    ->limit(40),
                Tables\Columns\TextColumn::make('confirmed_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
