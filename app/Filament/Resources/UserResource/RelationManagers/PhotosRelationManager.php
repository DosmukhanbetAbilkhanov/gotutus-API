<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Enums\PhotoStatus;
use App\Models\UserPhoto;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PhotosRelationManager extends RelationManager
{
    protected static string $relationship = 'photos';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('photo_url')
                    ->label('Photo')
                    ->disk('public'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state): string => match ($state instanceof \BackedEnum ? $state->value : $state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('rejection_reason')
                    ->limit(50)
                    ->wrap(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                \Filament\Actions\Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (UserPhoto $record) => $record->status !== PhotoStatus::Approved)
                    ->action(function (UserPhoto $record) {
                        $record->update(['status' => PhotoStatus::Approved, 'rejection_reason' => null]);
                        Notification::make()->title('Photo approved')->success()->send();
                    }),
                \Filament\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (UserPhoto $record) => $record->status !== PhotoStatus::Rejected)
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->required(),
                    ])
                    ->action(function (UserPhoto $record, array $data) {
                        $record->update(['status' => PhotoStatus::Rejected, 'rejection_reason' => $data['rejection_reason']]);
                        Notification::make()->title('Photo rejected')->danger()->send();
                    }),
            ]);
    }
}
