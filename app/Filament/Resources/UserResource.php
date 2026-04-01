<?php

namespace App\Filament\Resources;

use App\Enums\Gender;
use App\Enums\UserStatus;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\City;
use App\Models\User;
use App\Models\UserType;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Database\Seeders\ProductionSeeder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';

    protected static string | \UnitEnum | null $navigationGroup = 'Users & Safety';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Client Users';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Section::make('User Info')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->disabled(),
                        Forms\Components\TextInput::make('email')
                            ->disabled(),
                        Forms\Components\TextInput::make('phone')
                            ->disabled(),
                        Forms\Components\TextInput::make('age')
                            ->disabled(),
                        Forms\Components\Select::make('gender')
                            ->options(collect(Gender::cases())->mapWithKeys(fn ($g) => [$g->value => ucfirst($g->value)]))
                            ->disabled(),
                        Forms\Components\Textarea::make('bio')
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                \Filament\Schemas\Components\Section::make('Admin Controls')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options(collect(UserStatus::cases())->mapWithKeys(fn ($s) => [$s->value => ucfirst($s->value)]))
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
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('age')
                    ->sortable(),
                Tables\Columns\TextColumn::make('gender')
                    ->badge()
                    ->sortable()
                    ->color(fn ($state): string => match ($state instanceof \BackedEnum ? $state->value : $state) {
                        'male' => 'info',
                        'female' => 'pink',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('city_name')
                    ->label('City')
                    ->getStateUsing(fn (User $record) => $record->city?->translations->firstWhere('language_code', 'en')?->name),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->color(fn ($state): string => match ($state instanceof \BackedEnum ? $state->value : $state) {
                        'active' => 'success',
                        'suspended' => 'warning',
                        'banned' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_online')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('photos_count')
                    ->counts('photos')
                    ->label('Photos')
                    ->sortable(),
                Tables\Columns\TextColumn::make('hangout_requests_count')
                    ->counts('hangoutRequests')
                    ->label('Hangouts')
                    ->sortable(),
                Tables\Columns\TextColumn::make('trust_score')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state): string => match (true) {
                        $state === null => 'gray',
                        (float) $state >= 4.0 => 'success',
                        (float) $state >= 3.0 => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 2) : 'N/A')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('average_rating')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 2) . ' / 5' : 'N/A')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('ratings_count')
                    ->sortable()
                    ->label('Ratings')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('attendance_rate')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 1) . '%' : 'N/A')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('reports_received_count')
                    ->counts('reportsReceived')
                    ->label('Reports')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('last_seen_at')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(UserStatus::cases())->mapWithKeys(fn ($s) => [$s->value => ucfirst($s->value)])),
                Tables\Filters\SelectFilter::make('gender')
                    ->options(collect(Gender::cases())->mapWithKeys(fn ($g) => [$g->value => ucfirst($g->value)])),
                Tables\Filters\SelectFilter::make('city_id')
                    ->label('City')
                    ->options(function () {
                        return City::with('translations')->get()->mapWithKeys(function ($city) {
                            $name = $city->translations->firstWhere('language_code', 'en')?->name ?? "City #{$city->id}";
                            return [$city->id => $name];
                        });
                    }),
                Tables\Filters\TernaryFilter::make('is_online')
                    ->label('Online'),
                Tables\Filters\Filter::make('test_users')
                    ->label('Test Users Only')
                    ->query(fn (Builder $query) => $query->where('email', 'like', '%'.ProductionSeeder::TEST_EMAIL_DOMAIN))
                    ->toggle(),
                Tables\Filters\Filter::make('has_reports')
                    ->label('Has Reports')
                    ->query(fn (Builder $query) => $query->has('reportsReceived'))
                    ->toggle(),
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
                \Filament\Actions\Action::make('suspend')
                    ->icon('heroicon-o-pause-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (User $record) => $record->status !== UserStatus::Suspended)
                    ->action(function (User $record) {
                        $record->update(['status' => UserStatus::Suspended]);
                        $record->tokens()->delete();
                        Notification::make()->title('User suspended and tokens revoked')->success()->send();
                    }),
                \Filament\Actions\Action::make('ban')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (User $record) => $record->status !== UserStatus::Banned)
                    ->action(function (User $record) {
                        $record->update(['status' => UserStatus::Banned]);
                        $record->tokens()->delete();
                        Notification::make()->title('User banned and tokens revoked')->success()->send();
                    }),
                \Filament\Actions\Action::make('activate')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (User $record) => $record->status !== UserStatus::Active)
                    ->action(function (User $record) {
                        $record->update(['status' => UserStatus::Active]);
                        Notification::make()->title('User activated')->success()->send();
                    }),
            ])
            ->headerActions([
                \Filament\Actions\Action::make('delete_all_test_users')
                    ->label('Delete All Test Users')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Delete All Test Users')
                    ->modalDescription('This will permanently delete ALL users with @companion.test email addresses and their related data (tokens, interests, photos, join requests, hangout requests, messages, reports, etc.). This action cannot be undone.')
                    ->action(function () {
                        $testUsers = User::where('email', 'like', '%'.ProductionSeeder::TEST_EMAIL_DOMAIN)->get();

                        if ($testUsers->isEmpty()) {
                            Notification::make()->title('No test users found')->warning()->send();
                            return;
                        }

                        $count = $testUsers->count();
                        foreach ($testUsers as $user) {
                            $user->tokens()->delete();
                            $user->delete();
                        }

                        Notification::make()
                            ->title("{$count} test users deleted")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\BulkAction::make('ban_selected')
                        ->label('Ban Selected')
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each(function (User $record) {
                                $record->update(['status' => UserStatus::Banned]);
                                $record->tokens()->delete();
                            });
                            Notification::make()->title('Selected users banned')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    \Filament\Actions\BulkAction::make('suspend_selected')
                        ->label('Suspend Selected')
                        ->icon('heroicon-o-pause-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each(function (User $record) {
                                $record->update(['status' => UserStatus::Suspended]);
                                $record->tokens()->delete();
                            });
                            Notification::make()->title('Selected users suspended')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    \Filament\Actions\BulkAction::make('activate_selected')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each(fn (User $record) => $record->update(['status' => UserStatus::Active]));
                            Notification::make()->title('Selected users activated')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    \Filament\Actions\BulkAction::make('delete_selected')
                        ->label('Delete Selected')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalDescription('Permanently delete selected users and all their related data. This cannot be undone.')
                        ->action(function (Collection $records) {
                            $count = $records->count();
                            $records->each(function (User $record) {
                                $record->tokens()->delete();
                                $record->delete();
                            });
                            Notification::make()->title("{$count} users deleted")->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PhotosRelationManager::class,
            RelationManagers\HangoutRequestsRelationManager::class,
            RelationManagers\ReportsReceivedRelationManager::class,
            RelationManagers\ReportsSentRelationManager::class,
            RelationManagers\BlockedUsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'view' => Pages\ViewUser::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('userType', fn (Builder $q) => $q->where('slug', UserType::SLUG_CLIENT))
            ->with(['city.translations']);
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
