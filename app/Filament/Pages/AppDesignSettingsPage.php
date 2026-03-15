<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\AppDesignSetting;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class AppDesignSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-paint-brush';

    protected static string | \UnitEnum | null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 10;

    protected static ?string $title = 'App Design Settings';

    protected static ?string $slug = 'app-design-settings';

    protected string $view = 'filament.pages.app-design-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $setting = AppDesignSetting::active();

        if (! $setting) {
            $defaults = AppDesignSetting::defaults();
            $setting = AppDesignSetting::create(array_merge($defaults, ['is_active' => true]));
        }

        $this->form->fill($this->flattenForForm($setting));
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Tabs::make('Design Settings')
                    ->tabs([
                        $this->colorsTab(),
                        $this->typographyTab(),
                        $this->spacingTab(),
                        $this->borderRadiusTab(),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    protected function colorsTab(): Tab
    {
        $colorFields = [
            'primary' => 'Primary',
            'primaryLight' => 'Primary Light',
            'secondary' => 'Secondary',
            'backgroundLight' => 'Background Light',
            'backgroundDark' => 'Background Dark',
            'textPrimary' => 'Text Primary',
            'textSecondary' => 'Text Secondary',
            'textTertiary' => 'Text Tertiary',
            'inputBackground' => 'Input Background',
            'border' => 'Border',
            'divider' => 'Divider',
            'success' => 'Success',
            'error' => 'Error',
            'warning' => 'Warning',
            'messageMine' => 'Message Mine',
            'messageTheirs' => 'Message Theirs',
        ];

        $fields = [];
        foreach ($colorFields as $key => $label) {
            $fields[] = ColorPicker::make("colors_{$key}")
                ->label($label)
                ->required();
        }

        return Tab::make('Colors')
            ->icon('heroicon-o-swatch')
            ->schema([
                Section::make('App Colors')
                    ->description('Configure the color palette for the mobile app.')
                    ->schema($fields)
                    ->columns(3),
            ]);
    }

    protected function typographyTab(): Tab
    {
        $textStyles = ['h1', 'h2', 'h3', 'bodyLarge', 'bodyMedium', 'bodySmall', 'labelLarge', 'labelMedium', 'labelSmall', 'button', 'caption'];

        $sections = [
            Section::make('Font Family')
                ->schema([
                    TextInput::make('typography_fontFamily')
                        ->label('Font Family')
                        ->required(),
                ])
                ->columns(1),
        ];

        foreach ($textStyles as $style) {
            $label = ucfirst(preg_replace('/([A-Z])/', ' $1', $style));

            $fields = [
                TextInput::make("typography_{$style}_fontSize")
                    ->label('Size')
                    ->numeric()
                    ->required(),
                TextInput::make("typography_{$style}_fontWeight")
                    ->label('Weight')
                    ->numeric()
                    ->required(),
                TextInput::make("typography_{$style}_lineHeight")
                    ->label('Line Height')
                    ->numeric()
                    ->step(0.01)
                    ->required(),
            ];

            // Only some styles have letterSpacing
            if (in_array($style, ['h1', 'h2'])) {
                $fields[] = TextInput::make("typography_{$style}_letterSpacing")
                    ->label('Letter Spacing')
                    ->numeric()
                    ->step(0.1);
            }

            $sections[] = Section::make($label)
                ->schema($fields)
                ->columns(count($fields))
                ->compact();
        }

        return Tab::make('Typography')
            ->icon('heroicon-o-language')
            ->schema($sections);
    }

    protected function spacingTab(): Tab
    {
        $spacingFields = [
            'inputHeight' => 'Input Height',
            'buttonHeight' => 'Button Height',
            'bottomNavHeight' => 'Bottom Nav Height',
            'fabSize' => 'FAB Size',
            'avatarSmall' => 'Avatar Small',
            'avatarMedium' => 'Avatar Medium',
            'avatarLarge' => 'Avatar Large',
            'inputPaddingH' => 'Input Padding H',
            'inputPaddingV' => 'Input Padding V',
            'chipPaddingH' => 'Chip Padding H',
            'chipPaddingV' => 'Chip Padding V',
        ];

        $fields = [];
        foreach ($spacingFields as $key => $label) {
            $fields[] = TextInput::make("spacing_{$key}")
                ->label($label)
                ->numeric()
                ->required();
        }

        return Tab::make('Spacing')
            ->icon('heroicon-o-arrows-pointing-out')
            ->schema([
                Section::make('Component Sizes & Padding')
                    ->description('Configure heights, sizes, and padding values (in logical pixels).')
                    ->schema($fields)
                    ->columns(4),
            ]);
    }

    protected function borderRadiusTab(): Tab
    {
        $radiusFields = [
            'default' => 'Default',
            'large' => 'Large',
            'xl' => 'XL',
            'full' => 'Full (Circle)',
        ];

        $fields = [];
        foreach ($radiusFields as $key => $label) {
            $fields[] = TextInput::make("border_radius_{$key}")
                ->label($label)
                ->numeric()
                ->required();
        }

        return Tab::make('Border Radius')
            ->icon('heroicon-o-stop')
            ->schema([
                Section::make('Border Radius Values')
                    ->description('Configure border radius values (in logical pixels).')
                    ->schema($fields)
                    ->columns(4),
            ]);
    }

    public function save(): void
    {
        $formData = $this->form->getState();
        $setting = AppDesignSetting::active();

        if (! $setting) {
            $setting = new AppDesignSetting(['is_active' => true]);
        }

        $setting->colors = $this->unflattenGroup($formData, 'colors');
        $setting->typography = $this->unflattenTypography($formData);
        $setting->spacing = $this->unflattenGroup($formData, 'spacing');
        $setting->border_radius = $this->unflattenGroup($formData, 'border_radius');
        $setting->save();

        \Illuminate\Support\Facades\Cache::forget('app_design_settings:active');

        Notification::make()
            ->title('Design settings saved')
            ->success()
            ->send();
    }

    protected function flattenForForm(AppDesignSetting $setting): array
    {
        $data = [];

        // Flatten colors
        foreach ($setting->colors as $key => $value) {
            $data["colors_{$key}"] = $value;
        }

        // Flatten typography
        foreach ($setting->typography as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $prop => $propValue) {
                    $data["typography_{$key}_{$prop}"] = $propValue;
                }
            } else {
                $data["typography_{$key}"] = $value;
            }
        }

        // Flatten spacing
        foreach ($setting->spacing as $key => $value) {
            $data["spacing_{$key}"] = $value;
        }

        // Flatten border_radius
        foreach ($setting->border_radius as $key => $value) {
            $data["border_radius_{$key}"] = $value;
        }

        return $data;
    }

    protected function unflattenGroup(array $formData, string $prefix): array
    {
        $result = [];

        foreach ($formData as $key => $value) {
            if (str_starts_with($key, "{$prefix}_")) {
                $fieldName = substr($key, strlen("{$prefix}_"));
                $result[$fieldName] = is_numeric($value) ? (float) $value : $value;
            }
        }

        return $result;
    }

    protected function unflattenTypography(array $formData): array
    {
        $result = [];
        $textStyles = ['h1', 'h2', 'h3', 'bodyLarge', 'bodyMedium', 'bodySmall', 'labelLarge', 'labelMedium', 'labelSmall', 'button', 'caption'];

        // Font family
        $result['fontFamily'] = $formData['typography_fontFamily'] ?? 'Inter';

        // Text styles
        foreach ($textStyles as $style) {
            $styleData = [];
            $props = ['fontSize', 'fontWeight', 'lineHeight', 'letterSpacing'];

            foreach ($props as $prop) {
                $key = "typography_{$style}_{$prop}";
                if (isset($formData[$key]) && $formData[$key] !== '' && $formData[$key] !== null) {
                    $styleData[$prop] = (float) $formData[$key];
                }
            }

            if (! empty($styleData)) {
                // Ensure fontWeight is integer
                if (isset($styleData['fontWeight'])) {
                    $styleData['fontWeight'] = (int) $styleData['fontWeight'];
                }
                $result[$style] = $styleData;
            }
        }

        return $result;
    }

    protected function getFormActions(): array
    {
        return [];
    }
}
