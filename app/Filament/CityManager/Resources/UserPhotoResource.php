<?php

namespace App\Filament\CityManager\Resources;

use App\Enums\PhotoStatus;
use App\Filament\CityManager\Resources\UserPhotoResource\Pages;
use App\Models\UserPhoto;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class UserPhotoResource extends Resource
{
    protected static ?string $model = UserPhoto::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-photo';

    protected static string | \UnitEnum | null $navigationGroup = 'Users';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'User Photos';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Section::make()
                    ->schema([
                        Forms\Components\Placeholder::make('photo_preview')
                            ->label('Photo')
                            ->content(fn (UserPhoto $record) => new \Illuminate\Support\HtmlString(
                                '<img src="' . asset('storage/' . $record->photo_url) . '" style="max-width: 300px; max-height: 300px; border-radius: 8px;" />'
                            )),
                        Forms\Components\Select::make('status')
                            ->options(collect(PhotoStatus::cases())->mapWithKeys(fn ($s) => [$s->value => ucfirst($s->value)]))
                            ->required(),
                        Forms\Components\Textarea::make('rejection_reason')
                            ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get): bool => $get('status') === 'rejected'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('photo_url')
                    ->label('Photo')
                    ->disk('public'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->color(fn ($state): string => match ($state instanceof \BackedEnum ? $state->value : $state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('rejection_reason')
                    ->limit(50)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(PhotoStatus::cases())->mapWithKeys(fn ($s) => [$s->value => ucfirst($s->value)]))
                    ->default('pending'),
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
                            ->label('Rejection Reason')
                            ->required(),
                    ])
                    ->action(function (UserPhoto $record, array $data) {
                        $record->update(['status' => PhotoStatus::Rejected, 'rejection_reason' => $data['rejection_reason']]);
                        Notification::make()->title('Photo rejected')->danger()->send();
                    }),
                \Filament\Actions\EditAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\BulkAction::make('approve_selected')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each(fn (UserPhoto $record) => $record->update(['status' => PhotoStatus::Approved, 'rejection_reason' => null]));
                            Notification::make()->title('Selected photos approved')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    \Filament\Actions\BulkAction::make('reject_selected')
                        ->label('Reject Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->form([
                            Forms\Components\Textarea::make('rejection_reason')
                                ->label('Rejection Reason')
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each(fn (UserPhoto $record) => $record->update([
                                'status' => PhotoStatus::Rejected,
                                'rejection_reason' => $data['rejection_reason'],
                            ]));
                            Notification::make()->title('Selected photos rejected')->danger()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserPhotos::route('/'),
            'edit' => Pages\EditUserPhoto::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('user', fn (Builder $q) => $q->where('city_id', auth()->user()->city_id));
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
