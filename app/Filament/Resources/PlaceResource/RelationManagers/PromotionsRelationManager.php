<?php

namespace App\Filament\Resources\PlaceResource\RelationManagers;

use App\Models\PlacePromotion;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PromotionsRelationManager extends RelationManager
{
    protected static string $relationship = 'promotions';

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\Select::make('day_of_week')
                    ->label('Day of Week')
                    ->options(PlacePromotion::DAY_NAMES)
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->label('Promotion Title')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g., 50% off cocktails'),
                Forms\Components\TextInput::make('discount_percent')
                    ->label('Discount %')
                    ->numeric()
                    ->nullable()
                    ->minValue(1)
                    ->maxValue(100)
                    ->suffix('%')
                    ->placeholder('Optional'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('day_of_week')
                    ->label('Day')
                    ->formatStateUsing(fn (int $state) => PlacePromotion::DAY_NAMES[$state] ?? $state)
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Promotion')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('discount_percent')
                    ->label('Discount')
                    ->suffix('%')
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('day_of_week')
            ->headerActions([
                \Filament\Actions\CreateAction::make(),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ]);
    }
}
