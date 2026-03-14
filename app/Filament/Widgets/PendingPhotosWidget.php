<?php

namespace App\Filament\Widgets;

use App\Enums\PhotoStatus;
use App\Models\UserPhoto;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Notifications\Notification;

class PendingPhotosWidget extends BaseWidget
{
    protected static ?string $heading = 'Pending Photos for Review';

    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                UserPhoto::query()
                    ->where('status', PhotoStatus::Pending)
                    ->with('user')
                    ->latest('created_at')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('photo_url')
                    ->label('Photo')
                    ->disk('public')
                    ->circular(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User'),
                Tables\Columns\TextColumn::make('created_at')
                    ->since(),
            ])
            ->actions([
                Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (UserPhoto $record) {
                        $record->update([
                            'status' => PhotoStatus::Approved,
                            'rejection_reason' => null,
                        ]);
                        Notification::make()
                            ->title('Photo approved')
                            ->success()
                            ->send();
                    }),
                Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required(),
                    ])
                    ->action(function (UserPhoto $record, array $data) {
                        $record->update([
                            'status' => PhotoStatus::Rejected,
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        Notification::make()
                            ->title('Photo rejected')
                            ->danger()
                            ->send();
                    }),
            ])
            ->paginated(false);
    }
}
