<?php

namespace App\Filament\Resources\ConversationResource\RelationManagers;

use App\Models\Message;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Sender')
                    ->searchable(),
                Tables\Columns\TextColumn::make('message')
                    ->limit(100)
                    ->wrap()
                    ->searchable(),
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Image'),
                Tables\Columns\IconColumn::make('deleted_for_everyone')
                    ->boolean()
                    ->label('Deleted'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'asc')
            ->actions([
                \Filament\Actions\Action::make('delete_for_everyone')
                    ->label('Delete for All')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Message $record) => !$record->deleted_for_everyone)
                    ->action(function (Message $record) {
                        $record->update(['deleted_for_everyone' => true]);
                        Notification::make()->title('Message deleted for everyone')->success()->send();
                    }),
            ]);
    }
}
