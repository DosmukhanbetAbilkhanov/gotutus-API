<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlaceResource\Pages;
use App\Filament\Resources\PlaceResource\RelationManagers;
use App\Models\ActivityType;
use App\Models\City;
use App\Models\Place;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PlaceResource extends Resource
{
    protected static ?string $model = Place::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-map-pin';

    protected static string | \UnitEnum | null $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\Select::make('city_id')
                    ->label('City')
                    ->options(function () {
                        return City::with('translations')->get()->mapWithKeys(function ($city) {
                            $name = $city->translations->firstWhere('language_code', 'en')?->name ?? "City #{$city->id}";
                            return [$city->id => $name];
                        });
                    })
                    ->required()
                    ->searchable(),
                Forms\Components\CheckboxList::make('activityTypes')
                    ->relationship('activityTypes', 'slug')
                    ->label('Activity Types')
                    ->columns(2),
                \Filament\Schemas\Components\Section::make('Logo & Contacts')
                    ->schema([
                        Forms\Components\FileUpload::make('logo_path')
                            ->label('Logo')
                            ->image()
                            ->disk('public')
                            ->directory('places/logos')
                            ->maxSize(2048)
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('400')
                            ->imageResizeTargetHeight('400'),
                        Forms\Components\TextInput::make('phone')
                            ->label('Phone')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('website')
                            ->label('Website')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('instagram')
                            ->label('Instagram')
                            ->maxLength(255)
                            ->placeholder('@username or full URL'),
                        Forms\Components\TextInput::make('two_gis_url')
                            ->label('2GIS URL')
                            ->url()
                            ->maxLength(255),
                    ])
                    ->columns(2),
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
                                Forms\Components\TextInput::make('address')
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('description')
                                    ->label('Description')
                                    ->rows(3)
                                    ->maxLength(1000),
                            ])
                            ->defaultItems(3)
                            ->minItems(3)
                            ->maxItems(3)
                            ->reorderable(false)
                            ->addable(false)
                            ->deletable(false),
                    ]),
                \Filament\Schemas\Components\Section::make('Photos')
                    ->schema([
                        Forms\Components\Repeater::make('photos')
                            ->relationship()
                            ->schema([
                                Forms\Components\FileUpload::make('path')
                                    ->label('Photo')
                                    ->image()
                                    ->disk('public')
                                    ->directory('places/photos')
                                    ->required()
                                    ->maxSize(5120),
                                Forms\Components\TextInput::make('sort_order')
                                    ->label('Sort Order')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),
                            ])
                            ->defaultItems(0)
                            ->reorderable(true)
                            ->addable(true)
                            ->deletable(true)
                            ->maxItems(20),
                    ]),
                \Filament\Schemas\Components\Section::make('Working Hours')
                    ->schema([
                        Forms\Components\Repeater::make('workingHours')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('day_of_week')
                                    ->options([
                                        0 => 'Monday',
                                        1 => 'Tuesday',
                                        2 => 'Wednesday',
                                        3 => 'Thursday',
                                        4 => 'Friday',
                                        5 => 'Saturday',
                                        6 => 'Sunday',
                                    ])
                                    ->required()
                                    ->distinct(),
                                Forms\Components\TextInput::make('open_time')
                                    ->label('Open Time (HH:MM)')
                                    ->placeholder('09:00')
                                    ->maxLength(5)
                                    ->helperText('Leave empty if closed'),
                                Forms\Components\TextInput::make('close_time')
                                    ->label('Close Time (HH:MM)')
                                    ->placeholder('22:00')
                                    ->maxLength(5)
                                    ->helperText('Leave empty if closed'),
                            ])
                            ->defaultItems(7)
                            ->minItems(7)
                            ->maxItems(7)
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
                Tables\Columns\ImageColumn::make('logo_path')
                    ->label('Logo')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(fn () => null),
                Tables\Columns\TextColumn::make('name_en')
                    ->label('Name (EN)')
                    ->getStateUsing(fn (Place $record) => $record->translations->firstWhere('language_code', 'en')?->name)
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('translations', fn ($q) => $q->where('language_code', 'en')->where('name', 'like', "%{$search}%"));
                    }),
                Tables\Columns\TextColumn::make('name_ru')
                    ->label('Name (RU)')
                    ->getStateUsing(fn (Place $record) => $record->translations->firstWhere('language_code', 'ru')?->name)
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('translations', fn ($q) => $q->where('language_code', 'ru')->where('name', 'like', "%{$search}%"));
                    }),
                Tables\Columns\TextColumn::make('address_en')
                    ->label('Address (EN)')
                    ->getStateUsing(fn (Place $record) => $record->translations->firstWhere('language_code', 'en')?->address)
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('translations', fn ($q) => $q->where('language_code', 'en')->where('address', 'like', "%{$search}%"));
                    }),
                Tables\Columns\TextColumn::make('city_name')
                    ->label('City')
                    ->getStateUsing(fn (Place $record) => $record->city?->translations->firstWhere('language_code', 'en')?->name),
                Tables\Columns\TextColumn::make('activity_types_list')
                    ->label('Activity Types')
                    ->getStateUsing(fn (Place $record) => $record->activityTypes->pluck('slug')->join(', ')),
                Tables\Columns\TextColumn::make('photos_count')
                    ->counts('photos')
                    ->label('Photos')
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount')
                    ->label('Active Discount')
                    ->getStateUsing(fn (Place $record) => $record->activeDiscount?->discount_percent ? "{$record->activeDiscount->discount_percent}%" : 'None'),
                Tables\Columns\TextColumn::make('hangout_requests_count')
                    ->counts('hangoutRequests')
                    ->label('Hangouts')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id')
            ->filters([
                Tables\Filters\SelectFilter::make('city_id')
                    ->label('City')
                    ->options(function () {
                        return City::with('translations')->get()->mapWithKeys(function ($city) {
                            $name = $city->translations->firstWhere('language_code', 'en')?->name ?? "City #{$city->id}";
                            return [$city->id => $name];
                        });
                    }),
                Tables\Filters\TernaryFilter::make('has_discount')
                    ->label('Has Active Discount')
                    ->queries(
                        true: fn ($query) => $query->whereHas('activeDiscount'),
                        false: fn ($query) => $query->whereDoesntHave('activeDiscount'),
                    ),
                Tables\Filters\TernaryFilter::make('has_activity_types')
                    ->label('Has Activity Types')
                    ->queries(
                        true: fn ($query) => $query->whereHas('activityTypes'),
                        false: fn ($query) => $query->whereDoesntHave('activityTypes'),
                    ),
                Tables\Filters\TernaryFilter::make('has_description')
                    ->label('Has Description')
                    ->queries(
                        true: fn ($query) => $query->whereHas('translations', fn ($q) => $q->whereNotNull('description')->where('description', '!=', '')),
                        false: fn ($query) => $query->whereDoesntHave('translations', fn ($q) => $q->whereNotNull('description')->where('description', '!=', '')),
                    ),
                Tables\Filters\TernaryFilter::make('has_photo')
                    ->label('Has Photo')
                    ->queries(
                        true: fn ($query) => $query->whereHas('photos'),
                        false: fn ($query) => $query->whereDoesntHave('photos'),
                    ),
                Tables\Filters\TernaryFilter::make('has_phone')
                    ->label('Has Phone')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('phone')->where('phone', '!=', ''),
                        false: fn ($query) => $query->where(fn ($q) => $q->whereNull('phone')->orWhere('phone', '')),
                    ),
                Tables\Filters\TernaryFilter::make('has_working_hours')
                    ->label('Has Working Hours')
                    ->queries(
                        true: fn ($query) => $query->whereHas('workingHours'),
                        false: fn ($query) => $query->whereDoesntHave('workingHours'),
                    ),
                Tables\Filters\TernaryFilter::make('has_instagram')
                    ->label('Has Instagram')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('instagram')->where('instagram', '!=', ''),
                        false: fn ($query) => $query->where(fn ($q) => $q->whereNull('instagram')->orWhere('instagram', '')),
                    ),
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
            RelationManagers\DiscountsRelationManager::class,
            RelationManagers\PromotionsRelationManager::class,
            RelationManagers\ActivityTypesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlaces::route('/'),
            'create' => Pages\CreatePlace::route('/create'),
            'edit' => Pages\EditPlace::route('/{record}/edit'),
        ];
    }
}
