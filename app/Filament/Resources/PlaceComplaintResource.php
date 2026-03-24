<?php

namespace App\Filament\Resources;

use App\Enums\PlaceComplaintStatus;
use App\Enums\PlaceComplaintType;
use App\Filament\Resources\PlaceComplaintResource\Pages;
use App\Models\PlaceComplaint;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PlaceComplaintResource extends Resource
{
    protected static ?string $model = PlaceComplaint::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static string | \UnitEnum | null $navigationGroup = 'Feedback';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Place Complaints';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Section::make('Complaint Details')
                    ->schema([
                        Forms\Components\TextInput::make('type')
                            ->disabled()
                            ->formatStateUsing(fn ($state) => $state instanceof PlaceComplaintType ? $state->value : $state),
                        Forms\Components\Textarea::make('description')
                            ->disabled()
                            ->columnSpanFull(),
                        Forms\Components\Select::make('status')
                            ->options(collect(PlaceComplaintStatus::cases())->mapWithKeys(fn ($s) => [$s->value => ucfirst(str_replace('_', ' ', $s->value))]))
                            ->required(),
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Admin Notes')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('place_name')
                    ->label('Place')
                    ->searchable()
                    ->getStateUsing(fn (PlaceComplaint $record) => $record->place?->translations->firstWhere('language_code', 'en')?->name ?? "Place #{$record->place_id}"),
                Tables\Columns\TextColumn::make('hangout_request_id')
                    ->label('Hangout')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Filed By')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state instanceof PlaceComplaintType ? $state->value : $state) {
                        'discount_not_honored' => 'Discount Not Honored',
                        'amenities_not_provided' => 'Amenities Not Provided',
                        'other' => 'Other',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state instanceof PlaceComplaintType ? $state->value : $state) {
                        'discount_not_honored' => 'danger',
                        'amenities_not_provided' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state): string => match ($state instanceof PlaceComplaintStatus ? $state->value : $state) {
                        'pending' => 'warning',
                        'under_review' => 'info',
                        'resolved' => 'success',
                        'dismissed' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state instanceof PlaceComplaintStatus ? $state->value : $state))),
                Tables\Columns\TextColumn::make('description')
                    ->limit(40)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(PlaceComplaintStatus::cases())->mapWithKeys(fn ($s) => [$s->value => ucfirst(str_replace('_', ' ', $s->value))])),
                Tables\Filters\SelectFilter::make('type')
                    ->options(collect(PlaceComplaintType::cases())->mapWithKeys(fn ($t) => [$t->value => ucfirst(str_replace('_', ' ', $t->value))])),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\Action::make('resolve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (PlaceComplaint $record) => ! in_array($record->status, [PlaceComplaintStatus::Resolved, PlaceComplaintStatus::Dismissed]))
                    ->action(function (PlaceComplaint $record) {
                        $record->update([
                            'status' => PlaceComplaintStatus::Resolved,
                            'resolved_at' => now(),
                            'resolved_by' => auth()->id(),
                        ]);
                        Notification::make()->title('Complaint resolved')->success()->send();
                    }),
                \Filament\Actions\Action::make('dismiss')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (PlaceComplaint $record) => ! in_array($record->status, [PlaceComplaintStatus::Resolved, PlaceComplaintStatus::Dismissed]))
                    ->action(function (PlaceComplaint $record) {
                        $record->update([
                            'status' => PlaceComplaintStatus::Dismissed,
                            'resolved_at' => now(),
                            'resolved_by' => auth()->id(),
                        ]);
                        Notification::make()->title('Complaint dismissed')->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlaceComplaints::route('/'),
            'edit' => Pages\EditPlaceComplaint::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['place.translations', 'user']);
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
