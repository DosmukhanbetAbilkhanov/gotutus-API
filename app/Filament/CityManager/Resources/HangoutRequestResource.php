<?php

namespace App\Filament\CityManager\Resources;

use App\Enums\BillSplit;
use App\Enums\HangoutRequestStatus;
use App\Filament\CityManager\Resources\HangoutRequestResource\Pages;
use App\Filament\CityManager\Resources\HangoutRequestResource\RelationManagers;
use App\Models\ActivityType;
use App\Models\HangoutRequest;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HangoutRequestResource extends Resource
{
    protected static ?string $model = HangoutRequest::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-calendar';

    protected static string | \UnitEnum | null $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Hangout Requests';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Section::make('Hangout Details')
                    ->schema([
                        Forms\Components\TextInput::make('user.name')
                            ->label('User')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn ($record) => $record?->user?->name),
                        Forms\Components\TextInput::make('date')
                            ->disabled(),
                        Forms\Components\TextInput::make('time')
                            ->disabled(),
                        Forms\Components\TextInput::make('max_participants')
                            ->disabled(),
                        Forms\Components\Select::make('bill_split')
                            ->label('Bill Split')
                            ->options(collect(BillSplit::cases())->mapWithKeys(fn ($s) => [$s->value => ucfirst(str_replace('_', ' ', $s->value))]))
                            ->disabled(),
                        Forms\Components\Textarea::make('notes')
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                \Filament\Schemas\Components\Section::make('Management')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options(collect(HangoutRequestStatus::cases())->mapWithKeys(fn ($s) => [$s->value => ucfirst($s->value)]))
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),
                Tables\Columns\TextColumn::make('activity_type_name')
                    ->label('Activity')
                    ->getStateUsing(fn (HangoutRequest $record) => $record->activityType?->translations->firstWhere('language_code', 'en')?->name ?? $record->activityType?->slug),
                Tables\Columns\TextColumn::make('place_name')
                    ->label('Place')
                    ->getStateUsing(fn (HangoutRequest $record) => $record->place?->translations->firstWhere('language_code', 'en')?->name),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('time')
                    ->time('H:i'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->color(fn ($state): string => match ($state instanceof \BackedEnum ? $state->value : $state) {
                        'open' => 'success',
                        'matched' => 'info',
                        'closed' => 'gray',
                        'completed' => 'primary',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('max_participants'),
                Tables\Columns\TextColumn::make('bill_split')
                    ->label('Bill Split')
                    ->getStateUsing(fn (HangoutRequest $record) => $record->bill_split?->value)
                    ->badge()
                    ->color(fn ($state): string => match ($state) {
                        'split_even' => 'info',
                        'pay_own' => 'gray',
                        'organizer_pays' => 'success',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('join_requests_count')
                    ->counts('joinRequests')
                    ->label('Joins')
                    ->sortable(),
                Tables\Columns\TextColumn::make('notes')
                    ->limit(30)
                    ->wrap()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(HangoutRequestStatus::cases())->mapWithKeys(fn ($s) => [$s->value => ucfirst($s->value)])),
                Tables\Filters\SelectFilter::make('activity_type_id')
                    ->label('Activity Type')
                    ->options(function () {
                        return ActivityType::with('translations')->get()->mapWithKeys(function ($at) {
                            $name = $at->translations->firstWhere('language_code', 'en')?->name ?? $at->slug;
                            return [$at->id => $name];
                        });
                    }),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $q, $date) => $q->whereDate('date', '>=', $date))
                            ->when($data['until'], fn (Builder $q, $date) => $q->whereDate('date', '<=', $date));
                    }),
                Tables\Filters\TernaryFilter::make('has_join_requests')
                    ->label('Has Join Requests')
                    ->queries(
                        true: fn (Builder $query) => $query->has('joinRequests'),
                        false: fn (Builder $query) => $query->doesntHave('joinRequests'),
                    ),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\Action::make('close')
                    ->icon('heroicon-o-lock-closed')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (HangoutRequest $record) => $record->status === HangoutRequestStatus::Open)
                    ->action(function (HangoutRequest $record) {
                        $record->update(['status' => HangoutRequestStatus::Closed]);
                        Notification::make()->title('Hangout closed')->success()->send();
                    }),
                \Filament\Actions\Action::make('cancel')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (HangoutRequest $record) => in_array($record->status, [HangoutRequestStatus::Open, HangoutRequestStatus::Matched]))
                    ->action(function (HangoutRequest $record) {
                        $record->update(['status' => HangoutRequestStatus::Cancelled]);
                        Notification::make()->title('Hangout cancelled')->success()->send();
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\JoinRequestsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHangoutRequests::route('/'),
            'edit' => Pages\EditHangoutRequest::route('/{record}/edit'),
            'view' => Pages\ViewHangoutRequest::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('city_id', auth()->user()->city_id)
            ->with(['user', 'activityType.translations', 'place.translations']);
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
