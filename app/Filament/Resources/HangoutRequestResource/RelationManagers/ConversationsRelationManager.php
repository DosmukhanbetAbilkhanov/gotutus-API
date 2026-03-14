<?php

namespace App\Filament\Resources\HangoutRequestResource\RelationManagers;

use App\Models\Conversation;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ConversationsRelationManager extends RelationManager
{
    protected static string $relationship = 'conversations';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('participants_list')
                    ->label('Participants')
                    ->getStateUsing(fn (Conversation $record) => $record->participants->pluck('name')->join(', ')),
                Tables\Columns\TextColumn::make('messages_count')
                    ->counts('messages')
                    ->label('Messages'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
