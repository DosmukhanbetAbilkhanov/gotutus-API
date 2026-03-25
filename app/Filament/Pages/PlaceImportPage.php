<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\City;
use App\Models\PlaceImport;
use App\Services\PlaceImportService;
use App\Services\TransliterationService;
use App\Services\WorkingHoursParserService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PlaceImportPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static string | \UnitEnum | null $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Import Places';

    protected static ?string $slug = 'place-import';

    protected string $view = 'filament.pages.place-import';

    public ?array $data = [];

    // State for preview/import results
    public ?array $previewData = null;

    public ?array $importResult = null;

    public bool $hasPreview = false;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Select::make('city_id')
                    ->label('City')
                    ->options(
                        City::query()
                            ->with('translations')
                            ->get()
                            ->mapWithKeys(function (City $city) {
                                $enTranslation = $city->translations->firstWhere('language_code', 'en');
                                $ruTranslation = $city->translations->firstWhere('language_code', 'ru');
                                $name = $enTranslation?->name ?? $ruTranslation?->name ?? "City #{$city->id}";

                                return [$city->id => $name];
                            })
                    )
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn () => $this->resetResults()),

                FileUpload::make('file')
                    ->label('Excel File')
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                        'text/csv',
                    ])
                    ->maxSize(10240) // 10MB
                    ->directory('place-imports')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn () => $this->resetResults()),
            ])
            ->statePath('data');
    }

    public function preview(): void
    {
        $this->form->validate();

        $formData = $this->form->getState();
        $cityId = (int) $formData['city_id'];

        $filePath = $this->getUploadedFilePath($formData);

        Log::info('PlaceImport preview: form data', [
            'city_id' => $cityId,
            'file_raw' => $formData['file'] ?? 'NULL',
            'resolved_path' => $filePath,
        ]);

        if (! $filePath) {
            Notification::make()
                ->title('File not found')
                ->body('Could not locate the uploaded file. Raw value: ' . json_encode($formData['file'] ?? null))
                ->danger()
                ->send();

            return;
        }

        try {
            $service = $this->makeImportService();
            $this->previewData = $service->preview($filePath, $cityId);
            $this->hasPreview = true;
            $this->importResult = null;
        } catch (\Throwable $e) {
            Log::error('PlaceImport preview failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            Notification::make()
                ->title('Preview failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function import(): void
    {
        $this->form->validate();

        $formData = $this->form->getState();
        $cityId = (int) $formData['city_id'];

        $filePath = $this->getUploadedFilePath($formData);
        if (! $filePath) {
            Notification::make()
                ->title('File not found')
                ->danger()
                ->send();

            return;
        }

        // Record the import in the log
        $importLog = PlaceImport::create([
            'city_id' => $cityId,
            'user_id' => Auth::id(),
            'file_name' => $this->getUploadedFileName($formData),
            'status' => 'processing',
        ]);

        try {
            $service = $this->makeImportService();
            $result = $service->import($filePath, $cityId);

            $importLog->update([
                'total_rows' => $result->totalRows,
                'imported_count' => $result->imported,
                'skipped_count' => $result->skipped,
                'failed_count' => $result->failed,
                'errors' => $result->errors,
                'warnings' => $result->warnings,
                'status' => $result->failed > 0 && $result->imported === 0 ? 'failed' : 'completed',
            ]);

            $this->importResult = $result->toArray();
            $this->hasPreview = false;

            Notification::make()
                ->title('Import completed')
                ->body("Imported: {$result->imported}, Skipped: {$result->skipped}, Failed: {$result->failed}")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            $importLog->update([
                'status' => 'failed',
                'errors' => [$e->getMessage()],
            ]);

            Notification::make()
                ->title('Import failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    private function resetResults(): void
    {
        $this->previewData = null;
        $this->importResult = null;
        $this->hasPreview = false;
    }

    private function makeImportService(): PlaceImportService
    {
        return new PlaceImportService(
            new TransliterationService(),
            new WorkingHoursParserService(),
        );
    }

    private function getUploadedFilePath(array $formData): ?string
    {
        $file = $formData['file'] ?? null;

        if (empty($file)) {
            return null;
        }

        $fileName = is_array($file) ? reset($file) : $file;

        if (! $fileName) {
            return null;
        }

        // Filament FileUpload stores relative to the default disk (storage/app/private in Laravel 11+)
        // The fileName already includes the directory prefix (e.g. "place-imports/xxx.xlsx")
        foreach (['local', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($fileName)) {
                return Storage::disk($disk)->path($fileName);
            }
        }

        // Fallback: check default disk
        if (Storage::exists($fileName)) {
            return Storage::path($fileName);
        }

        Log::warning('PlaceImport: file not found', ['fileName' => $fileName]);

        return null;
    }

    private function getUploadedFileName(array $formData): string
    {
        $file = $formData['file'] ?? null;

        if (empty($file)) {
            return 'unknown';
        }

        $fileName = is_array($file) ? reset($file) : $file;

        return $fileName ?? 'unknown';
    }

    protected function getFormActions(): array
    {
        return [];
    }
}
