<?php

namespace App\Filament\Resources;

use App\Enums\ReportStatus;
use App\Enums\UserStatus;
use App\Filament\Resources\ReportResource\Pages;
use App\Models\Report;
use App\Models\User;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-flag';

    protected static string | \UnitEnum | null $navigationGroup = 'Users & Safety';

    protected static ?int $navigationSort = 4;

    public static function getNavigationBadge(): ?string
    {
        $count = Report::where('status', ReportStatus::Pending)->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Section::make('Report Details')
                    ->schema([
                        Forms\Components\TextInput::make('reporter_name')
                            ->label('Reporter')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn ($record) => $record?->reporter?->name),
                        Forms\Components\TextInput::make('reported_user_name')
                            ->label('Reported User')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn ($record) => $record?->reportedUser?->name),
                        Forms\Components\Textarea::make('reason')
                            ->disabled()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('hangout_request_id')
                            ->label('Hangout Request ID')
                            ->disabled(),
                    ])
                    ->columns(2),
                \Filament\Schemas\Components\Section::make('Review')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options(collect(ReportStatus::cases())->mapWithKeys(fn ($s) => [$s->value => ucfirst(str_replace('_', ' ', $s->value))]))
                            ->required(),
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Admin Notes')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reporter.name')
                    ->label('Reporter')
                    ->searchable()
                    ->url(fn (Report $record): string => UserResource::getUrl('view', ['record' => $record->reporter_id])),
                Tables\Columns\TextColumn::make('reportedUser.name')
                    ->label('Reported User')
                    ->searchable()
                    ->url(fn (Report $record): string => UserResource::getUrl('view', ['record' => $record->reported_user_id])),
                Tables\Columns\TextColumn::make('hangoutRequest.id')
                    ->label('Hangout #')
                    ->url(fn (Report $record): ?string => $record->hangout_request_id ? HangoutRequestResource::getUrl('view', ['record' => $record->hangout_request_id]) : null),
                Tables\Columns\TextColumn::make('reason')
                    ->searchable()
                    ->wrap()
                    ->limit(60),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->color(fn ($state): string => match ($state instanceof \BackedEnum ? $state->value : $state) {
                        'pending' => 'warning',
                        'reviewed' => 'info',
                        'action_taken' => 'success',
                        'dismissed' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('admin_notes')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('reviewedBy.name')
                    ->label('Reviewed By')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('reviewed_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(ReportStatus::cases())->mapWithKeys(fn ($s) => [$s->value => ucfirst(str_replace('_', ' ', $s->value))]))
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
                \Filament\Actions\EditAction::make()
                    ->label('Review'),
                \Filament\Actions\Action::make('dismiss')
                    ->icon('heroicon-o-x-mark')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (Report $record) => $record->status === ReportStatus::Pending)
                    ->action(function (Report $record) {
                        $record->update([
                            'status' => ReportStatus::Dismissed,
                            'reviewed_by' => Auth::id(),
                            'reviewed_at' => now(),
                        ]);
                        Notification::make()->title('Report dismissed')->success()->send();
                    }),
                \Filament\Actions\Action::make('ban_user')
                    ->label('Ban User')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('This will ban the reported user and mark this report as action taken.')
                    ->visible(fn (Report $record) => $record->status === ReportStatus::Pending)
                    ->action(function (Report $record) {
                        $record->reportedUser->update(['status' => UserStatus::Banned]);
                        $record->reportedUser->tokens()->delete();
                        $record->update([
                            'status' => ReportStatus::ActionTaken,
                            'reviewed_by' => Auth::id(),
                            'reviewed_at' => now(),
                            'admin_notes' => ($record->admin_notes ? $record->admin_notes . "\n" : '') . 'User banned.',
                        ]);
                        Notification::make()->title('User banned and report resolved')->success()->send();
                    }),
                \Filament\Actions\Action::make('suspend_user')
                    ->label('Suspend User')
                    ->icon('heroicon-o-pause-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalDescription('This will suspend the reported user and mark this report as action taken.')
                    ->visible(fn (Report $record) => $record->status === ReportStatus::Pending)
                    ->action(function (Report $record) {
                        $record->reportedUser->update(['status' => UserStatus::Suspended]);
                        $record->update([
                            'status' => ReportStatus::ActionTaken,
                            'reviewed_by' => Auth::id(),
                            'reviewed_at' => now(),
                            'admin_notes' => ($record->admin_notes ? $record->admin_notes . "\n" : '') . 'User suspended.',
                        ]);
                        Notification::make()->title('User suspended and report resolved')->success()->send();
                    }),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\BulkAction::make('dismiss_selected')
                        ->label('Dismiss Selected')
                        ->icon('heroicon-o-x-mark')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each(fn (Report $record) => $record->update([
                                'status' => ReportStatus::Dismissed,
                                'reviewed_by' => Auth::id(),
                                'reviewed_at' => now(),
                            ]));
                            Notification::make()->title('Selected reports dismissed')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReports::route('/'),
            'edit' => Pages\EditReport::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['reporter', 'reportedUser', 'reviewedBy']);
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
