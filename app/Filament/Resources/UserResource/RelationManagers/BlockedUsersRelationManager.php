<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class BlockedUsersRelationManager extends RelationManager
{
    protected static string $relationship = 'blockedUsers';

    protected static ?string $title = 'Blocked Users';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('blockedUser.name')
                    ->label('Blocked User'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                \Filament\Actions\DeleteAction::make()
                    ->label('Unblock'),
            ]);
    }
}
