<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityTypeResource\Pages;
use App\Filament\Resources\ActivityTypeResource\RelationManagers;
use App\Models\ActivityType;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ActivityTypeResource extends Resource
{
    protected static ?string $model = ActivityType::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string | \UnitEnum | null $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
                Forms\Components\FileUpload::make('bg_photo')
                    ->label('Background Photo')
                    ->image()
                    ->directory('activity-types'),
                Forms\Components\TextInput::make('icon')
                    ->maxLength(255),
                \Filament\Schemas\Components\Section::make('Translations')
                    ->schema([
                        Forms\Components\Repeater::make('translations')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('language_code')
                                    ->options([
                                        'en' => 'English',
                                        'ru' => 'Russian',
                                        'kk' => 'Kazakh',
                                    ])
                                    ->required()
                                    ->distinct(),
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->defaultItems(3)
                            ->minItems(3)
                            ->maxItems(3)
                            ->reorderable(false)
                            ->addable(false)
                            ->deletable(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name_en')
                    ->label('Name (EN)')
                    ->getStateUsing(fn (ActivityType $record) => $record->translations->firstWhere('language_code', 'en')?->name)
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('translations', fn ($q) => $q->where('language_code', 'en')->where('name', 'like', "%{$search}%"));
                    }),
                Tables\Columns\TextColumn::make('name_ru')
                    ->label('Name (RU)')
                    ->getStateUsing(fn (ActivityType $record) => $record->translations->firstWhere('language_code', 'ru')?->name)
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('translations', fn ($q) => $q->where('language_code', 'ru')->where('name', 'like', "%{$search}%"));
                    }),
                Tables\Columns\TextColumn::make('name_kk')
                    ->label('Name (KK)')
                    ->getStateUsing(fn (ActivityType $record) => $record->translations->firstWhere('language_code', 'kk')?->name)
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('translations', fn ($q) => $q->where('language_code', 'kk')->where('name', 'like', "%{$search}%"));
                    }),
                Tables\Columns\ImageColumn::make('bg_photo')
                    ->label('Photo'),
                Tables\Columns\TextColumn::make('icon'),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->sortable(),
                Tables\Columns\TextColumn::make('hangout_requests_count')
                    ->counts('hangoutRequests')
                    ->label('Hangouts')
                    ->sortable(),
            ])
            ->defaultSort('id')
            ->filters([
                //
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PlacesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityTypes::route('/'),
            'create' => Pages\CreateActivityType::route('/create'),
            'edit' => Pages\EditActivityType::route('/{record}/edit'),
        ];
    }
}
