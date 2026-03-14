<?php

namespace App\Filament\Resources;

use App\Enums\JoinRequestStatus;
use App\Filament\Resources\JoinRequestResource\Pages;
use App\Models\JoinRequest;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class JoinRequestResource extends Resource
{
    protected static ?string $model = JoinRequest::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-hand-raised';

    protected static string | \UnitEnum | null $navigationGroup = 'Hangouts';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Join Requests';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Section::make('Details')
                    ->schema([
                        Forms\Components\TextInput::make('user_name')
                            ->label('User')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn ($record) => $record?->user?->name),
                        Forms\Components\TextInput::make('hangout_request_id')
                            ->label('Hangout Request ID')
                            ->disabled(),
                        Forms\Components\Textarea::make('message')
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                \Filament\Schemas\Components\Section::make('Admin Controls')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options(collect(JoinRequestStatus::cases())->mapWithKeys(fn ($s) => [$s->value => ucfirst($s->value)]))
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
                    ->searchable()
                    ->url(fn (JoinRequest $record): string => UserResource::getUrl('view', ['record' => $record->user_id])),
                Tables\Columns\TextColumn::make('hangoutRequest.id')
                    ->label('Hangout #')
                    ->url(fn (JoinRequest $record): string => HangoutRequestResource::getUrl('view', ['record' => $record->hangout_request_id])),
                Tables\Columns\TextColumn::make('activity_type')
                    ->label('Activity')
                    ->getStateUsing(fn (JoinRequest $record) => $record->hangoutRequest?->activityType?->translations->firstWhere('language_code', 'en')?->name),
                Tables\Columns\TextColumn::make('suggested_place_name')
                    ->label('Suggested Place')
                    ->getStateUsing(fn (JoinRequest $record) => $record->suggestedPlace?->translations->firstWhere('language_code', 'en')?->name),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->color(fn ($state): string => match ($state instanceof \BackedEnum ? $state->value : $state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'confirmed' => 'primary',
                        'declined' => 'danger',
                        'cancelled' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('message')
                    ->limit(40)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('confirmed_at')
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
                    ->options(collect(JoinRequestStatus::cases())->mapWithKeys(fn ($s) => [$s->value => ucfirst($s->value)])),
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
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJoinRequests::route('/'),
            'edit' => Pages\EditJoinRequest::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user', 'hangoutRequest.activityType.translations', 'suggestedPlace.translations']);
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
