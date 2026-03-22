<?php

namespace App\Filament\CityManager\Resources;

use App\Filament\CityManager\Resources\PlaceAdvertisementResource\Pages;
use App\Models\ActivityType;
use App\Models\Place;
use App\Models\PlaceAdvertisement;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PlaceAdvertisementResource extends Resource
{
    protected static ?string $model = PlaceAdvertisement::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-megaphone';

    protected static string | \UnitEnum | null $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Place Advertisements';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\Select::make('place_id')
                    ->label('Place')
                    ->options(function () {
                        $cityId = auth()->user()->city_id;

                        return Place::with('translations')
                            ->where('city_id', $cityId)
                            ->get()
                            ->mapWithKeys(function ($place) {
                                $name = $place->translations->firstWhere('language_code', 'en')?->name ?? "Place #{$place->id}";
                                return [$place->id => $name];
                            });
                    })
                    ->required()
                    ->searchable(),

                Forms\Components\Select::make('activity_type_id')
                    ->label('Activity Type (optional)')
                    ->options(function () {
                        return ActivityType::with('translations')->get()->mapWithKeys(function ($type) {
                            $name = $type->translations->firstWhere('language_code', 'en')?->name ?? $type->slug;
                            return [$type->id => $name];
                        });
                    })
                    ->searchable()
                    ->nullable()
                    ->helperText('If set, ad only shows when filtering by this activity type. If null, shows in unfiltered feed.'),

                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('description')
                    ->maxLength(1000),

                Forms\Components\TextInput::make('button_text')
                    ->default('Create Hangout')
                    ->maxLength(100),

                Forms\Components\Select::make('media_type')
                    ->options(['image' => 'Image', 'video' => 'Video'])
                    ->required()
                    ->default('image'),

                Forms\Components\FileUpload::make('media_path')
                    ->label('Media')
                    ->disk('public')
                    ->directory('advertisements')
                    ->acceptedFileTypes(['image/*', 'video/mp4', 'video/quicktime'])
                    ->maxSize(20480)
                    ->required(),

                Forms\Components\Toggle::make('is_active')
                    ->default(true),

                Forms\Components\DateTimePicker::make('starts_at')
                    ->nullable(),

                Forms\Components\DateTimePicker::make('ends_at')
                    ->nullable(),

                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->helperText('Lower numbers appear first. Ads with same sort_order are rotated.'),
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
                    ->getStateUsing(fn (PlaceAdvertisement $record) => $record->place?->translations->firstWhere('language_code', 'en')?->name)
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('place.translations', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                    }),
                Tables\Columns\TextColumn::make('activity_type_name')
                    ->label('Activity Type')
                    ->getStateUsing(fn (PlaceAdvertisement $record) => $record->activityType?->translations->firstWhere('language_code', 'en')?->name ?? 'General'),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('media_type')
                    ->badge(),
                Tables\Columns\ImageColumn::make('media_path')
                    ->label('Media')
                    ->disk('public')
                    ->width(60)
                    ->height(40),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->sortable(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ends_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlaceAdvertisements::route('/'),
            'create' => Pages\CreatePlaceAdvertisement::route('/create'),
            'edit' => Pages\EditPlaceAdvertisement::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('city_id', auth()->user()->city_id);
    }

}
