<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\PlaceImportResult;
use App\Models\Place;
use App\Models\PlaceTranslation;
use App\Models\PlaceWorkingHour;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PlaceImportService
{
    /**
     * Excel column mapping (8-column file):
     *
     * A: Name (Russian)
     * B: Address (Russian)
     * C: Phone
     * D: Website
     * E: Working hours
     * F: Instagram
     * G: Latitude
     * H: Longitude
     */
    private const COL_NAME = 'A';

    private const COL_ADDRESS = 'B';

    private const COL_PHONE = 'C';

    private const COL_WEBSITE = 'D';

    private const COL_WORKING_HOURS = 'E';

    private const COL_INSTAGRAM = 'F';

    private const COL_LATITUDE = 'G';

    private const COL_LONGITUDE = 'H';

    public function __construct(
        private readonly TransliterationService $transliterationService,
        private readonly WorkingHoursParserService $workingHoursParser,
    ) {}

    /**
     * Import places from an Excel file into the given city.
     */
    public function import(string $filePath, int $cityId, ?array $options = []): PlaceImportResult
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $totalRows = $highestRow - 1; // Exclude header
        $imported = 0;
        $skipped = 0;
        $failed = 0;
        $errors = [];
        $warnings = [];

        DB::beginTransaction();

        try {
            for ($row = 2; $row <= $highestRow; $row++) {
                $name = $this->getCellValue($sheet, self::COL_NAME, $row);

                // Skip rows without a name (empty rows)
                if ($name === null || trim($name) === '') {
                    $skipped++;

                    continue;
                }

                $name = trim($name);
                $address = $this->getCellValue($sheet, self::COL_ADDRESS, $row);
                $address = $address !== null ? trim($address) : null;

                // Duplicate detection: same Russian name + address in same city
                if ($this->isDuplicate($cityId, $name, $address)) {
                    $skipped++;

                    continue;
                }

                try {
                    $this->createPlace($sheet, $row, $cityId, $name, $address, $warnings);
                    $imported++;
                } catch (\Throwable $e) {
                    $failed++;
                    $errors[] = "Row {$row}: {$e->getMessage()}";
                    Log::error("PlaceImport: failed on row {$row}", [
                        'error' => $e->getMessage(),
                        'name' => $name,
                    ]);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('PlaceImport: transaction failed', ['error' => $e->getMessage()]);

            return new PlaceImportResult(
                totalRows: $totalRows,
                imported: 0,
                skipped: 0,
                failed: $totalRows,
                errors: ["Transaction failed: {$e->getMessage()}"],
                warnings: [],
            );
        }

        return new PlaceImportResult(
            totalRows: $totalRows,
            imported: $imported,
            skipped: $skipped,
            failed: $failed,
            errors: $errors,
            warnings: $warnings,
        );
    }

    /**
     * Preview the file without importing. Returns parsed data summary.
     *
     * @return array{total_rows: int, preview_rows: array, warnings: array, errors: array}
     */
    public function preview(string $filePath, int $cityId, int $limit = 20): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $totalRows = $highestRow - 1;

        $previewRows = [];
        $warnings = [];
        $errors = [];
        $validCount = 0;
        $warningCount = 0;
        $errorCount = 0;

        for ($row = 2; $row <= $highestRow; $row++) {
            $name = $this->getCellValue($sheet, self::COL_NAME, $row);
            $address = $this->getCellValue($sheet, self::COL_ADDRESS, $row);
            $phone = $this->cleanPhone($this->getCellValue($sheet, self::COL_PHONE, $row));
            $website = $this->getCellValue($sheet, self::COL_WEBSITE, $row);
            $workingHoursRaw = $this->getCellValue($sheet, self::COL_WORKING_HOURS, $row);
            $instagram = $this->getCellValue($sheet, self::COL_INSTAGRAM, $row);
            $latitude = $this->getCellValue($sheet, self::COL_LATITUDE, $row);
            $longitude = $this->getCellValue($sheet, self::COL_LONGITUDE, $row);

            $rowStatus = 'OK';
            $rowErrors = [];
            $rowWarnings = [];

            // Name is the only required field
            if ($name === null || trim($name) === '') {
                $errors[] = "Row {$row}: missing name (skipped)";
                $errorCount++;
                $rowStatus = 'Error';
            } else {
                $isDuplicate = $this->isDuplicate($cityId, trim($name), $address !== null ? trim($address) : null);
                if ($isDuplicate) {
                    $warningCount++;
                    $rowStatus = 'Warning';
                    $rowWarnings[] = "Row {$row}: duplicate, will be skipped";
                    $warnings = array_merge($warnings, $rowWarnings);
                } else {
                    $validCount++;
                }
            }

            if (count($previewRows) < $limit) {
                $previewRows[] = [
                    'row' => $row,
                    'name' => $name ? trim($name) : '',
                    'address' => $address ? trim($address) : '',
                    'phone' => $phone ?? '',
                    'website' => $website ? trim($website) : '',
                    'instagram' => $instagram ? trim($instagram) : '',
                    'working_hours' => $workingHoursRaw ? trim($workingHoursRaw) : '',
                    'latitude' => $latitude ?? '',
                    'longitude' => $longitude ?? '',
                    'status' => $rowStatus,
                ];
            }
        }

        return [
            'total_rows' => $totalRows,
            'valid_count' => $validCount,
            'warning_count' => $warningCount,
            'error_count' => $errorCount,
            'preview_rows' => $previewRows,
            'warnings' => array_slice($warnings, 0, 50),
            'errors' => array_slice($errors, 0, 50),
        ];
    }

    /**
     * Create a single place with translations and working hours.
     */
    private function createPlace(
        $sheet,
        int $row,
        int $cityId,
        string $name,
        ?string $address,
        array &$warnings,
    ): Place {
        $phone = $this->cleanPhone($this->getCellValue($sheet, self::COL_PHONE, $row));
        $website = $this->getCellValue($sheet, self::COL_WEBSITE, $row);
        $instagram = $this->getCellValue($sheet, self::COL_INSTAGRAM, $row);
        $latitude = $this->getCellValue($sheet, self::COL_LATITUDE, $row);
        $longitude = $this->getCellValue($sheet, self::COL_LONGITUDE, $row);
        $workingHoursRaw = $this->getCellValue($sheet, self::COL_WORKING_HOURS, $row);

        // Clean instagram handle
        $instagramClean = null;
        if ($instagram !== null && trim($instagram) !== '') {
            $instagramClean = trim($instagram);
            $instagramClean = preg_replace('#^https?://(www\.)?instagram\.com/#', '', $instagramClean);
            $instagramClean = rtrim($instagramClean, '/');
        }

        // Create place
        $place = Place::create([
            'city_id' => $cityId,
            'latitude' => $latitude ? (float) $latitude : null,
            'longitude' => $longitude ? (float) $longitude : null,
            'phone' => $phone,
            'website' => $website ? trim($website) : null,
            'instagram' => $instagramClean,
        ]);

        // Create translations (ru, en, kk)
        $nameEn = $this->transliterationService->transliterate($name);
        $addressEn = $address ? $this->transliterationService->transliterate($address) : null;

        PlaceTranslation::create([
            'place_id' => $place->id,
            'language_code' => 'ru',
            'name' => $name,
            'address' => $address,
        ]);

        PlaceTranslation::create([
            'place_id' => $place->id,
            'language_code' => 'en',
            'name' => $nameEn,
            'address' => $addressEn,
        ]);

        // Kazakh: same as Russian (Cyrillic script)
        PlaceTranslation::create([
            'place_id' => $place->id,
            'language_code' => 'kk',
            'name' => $name,
            'address' => $address,
        ]);

        // Parse and create working hours
        if ($workingHoursRaw !== null && trim($workingHoursRaw) !== '') {
            $workingHours = $this->workingHoursParser->parse($workingHoursRaw);
            foreach ($workingHours as $dayHours) {
                if ($dayHours['open_time'] !== null && $dayHours['close_time'] !== null) {
                    PlaceWorkingHour::create([
                        'place_id' => $place->id,
                        'day_of_week' => $dayHours['day_of_week'],
                        'open_time' => $dayHours['open_time'],
                        'close_time' => $dayHours['close_time'],
                    ]);
                }
            }
        }

        return $place;
    }

    /**
     * Check if a place with the same Russian name + address already exists in the city.
     */
    private function isDuplicate(int $cityId, string $name, ?string $address): bool
    {
        $query = PlaceTranslation::query()
            ->where('language_code', 'ru')
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower(trim($name))])
            ->whereHas('place', fn ($q) => $q->where('city_id', $cityId));

        if ($address !== null && trim($address) !== '') {
            $query->whereRaw('LOWER(TRIM(address)) = ?', [mb_strtolower(trim($address))]);
        }

        return $query->exists();
    }

    /**
     * Clean a phone number: remove non-digit characters except +.
     */
    private function cleanPhone(?string $phone): ?string
    {
        if ($phone === null || trim((string) $phone) === '' || trim((string) $phone) === '-') {
            return null;
        }

        $cleaned = preg_replace('/[^\d+]/', '', trim((string) $phone));

        return $cleaned !== '' ? $cleaned : null;
    }

    /**
     * Get cell value as string or null.
     */
    private function getCellValue($sheet, string $col, int $row): ?string
    {
        $value = $sheet->getCell($col . $row)->getValue();
        if ($value === null) {
            return null;
        }

        $str = trim((string) $value);

        return $str !== '' ? $str : null;
    }
}
