<?php

namespace App\Filament\CityManager\Resources;

use App\Enums\PhotoStatus;
use App\Filament\CityManager\Resources\PhotoVerificationResource\Pages;
use App\Models\PhotoVerification;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PhotoVerificationResource extends Resource
{
    protected static ?string $model = PhotoVerification::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-check-badge';

    protected static string | \UnitEnum | null $navigationGroup = 'Users';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Photo Verifications';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Section::make('Verification Review')
                    ->schema([
                        Forms\Components\Placeholder::make('pose_instruction')
                            ->label('Requested pose')
                            ->content(fn (PhotoVerification $record) => $record->pose?->label() ?? '—'),
                        Forms\Components\Placeholder::make('selfie_preview')
                            ->label('Submitted selfie')
                            ->content(fn (PhotoVerification $record) => new \Illuminate\Support\HtmlString(
                                '<img src="' . asset('storage/' . $record->selfie_path) . '" style="max-width: 320px; max-height: 320px; border-radius: 8px;" />'
                            )),
                        Forms\Components\Placeholder::make('profile_photos')
                            ->label('Approved profile photos')
                            ->content(function (PhotoVerification $record) {
                                $photos = $record->user->photos()->approved()->get();

                                if ($photos->isEmpty()) {
                                    return new \Illuminate\Support\HtmlString('<em>No approved profile photos.</em>');
                                }

                                $imgs = $photos->map(fn ($p) =>
                                    '<img src="' . asset('storage/' . $p->photo_url) . '" style="width: 150px; height: 150px; object-fit: cover; border-radius: 8px; margin-right: 8px;" />'
                                )->implode('');

                                return new \Illuminate\Support\HtmlString('<div style="display:flex; flex-wrap:wrap; gap:8px;">' . $imgs . '</div>');
                            }),
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
                Tables\Columns\ImageColumn::make('selfie_path')
                    ->label('Selfie')
                    ->disk('public'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pose')
                    ->label('Pose')
                    ->formatStateUsing(fn ($state) => $state instanceof \BackedEnum ? $state->value : $state),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->color(fn ($state): string => match ($state instanceof \BackedEnum ? $state->value : $state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(PhotoStatus::cases())->mapWithKeys(fn ($s) => [$s->value => ucfirst($s->value)]))
                    ->default('pending'),
            ])
            ->actions([
                \Filament\Actions\Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (PhotoVerification $record) => $record->status !== PhotoStatus::Approved)
                    ->action(function (PhotoVerification $record) {
                        $record->update([
                            'status' => PhotoStatus::Approved,
                            'rejection_reason' => null,
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);
                        Notification::make()->title('Verification approved')->success()->send();
                    }),
                \Filament\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (PhotoVerification $record) => $record->status !== PhotoStatus::Rejected)
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required(),
                    ])
                    ->action(function (PhotoVerification $record, array $data) {
                        $record->update([
                            'status' => PhotoStatus::Rejected,
                            'rejection_reason' => $data['rejection_reason'],
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);
                        Notification::make()->title('Verification rejected')->danger()->send();
                    }),
                \Filament\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPhotoVerifications::route('/'),
            'edit' => Pages\EditPhotoVerification::route('/{record}/edit'),
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
