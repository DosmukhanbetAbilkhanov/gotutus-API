<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MessageResource\Pages;
use App\Models\Message;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MessageResource extends Resource
{
    protected static ?string $model = Message::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    protected static string | \UnitEnum | null $navigationGroup = 'Chat';

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Sender')
                    ->searchable(),
                Tables\Columns\TextColumn::make('conversation_id')
                    ->label('Conv #')
                    ->url(fn (Message $record): string => ConversationResource::getUrl('view', ['record' => $record->conversation_id])),
                Tables\Columns\TextColumn::make('message')
                    ->limit(100)
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
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('deleted_for_everyone')
                    ->label('Deleted'),
                Tables\Filters\TernaryFilter::make('has_image')
                    ->label('Has Image')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('image_url'),
                        false: fn (Builder $query) => $query->whereNull('image_url'),
                    ),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'], fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMessages::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
