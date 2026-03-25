<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class InspectPlacesFile extends Command
{
    protected $signature = 'places:inspect {file : Path to the Excel/CSV file}';

    protected $description = 'Inspect a places file and print column headers + sample rows';

    public function handle(): void
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");

            return;
        }

        $this->info("Reading file: {$filePath}");

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

        $this->info("Sheet: {$sheet->getTitle()}");
        $this->info("Rows: {$highestRow}, Columns: {$highestColumn} ({$highestColumnIndex})");
        $this->newLine();

        // Print headers (row 1)
        $this->info('=== COLUMN HEADERS (Row 1) ===');
        $headers = [];
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $value = $sheet->getCell($colLetter . '1')->getValue();
            $headers[$colLetter] = $value;
            $this->line("  Column {$colLetter} ({$col}): {$value}");
        }

        $this->newLine();

        // Print first 5 data rows
        $sampleRows = min(5, $highestRow - 1);
        $this->info("=== FIRST {$sampleRows} DATA ROWS ===");

        for ($row = 2; $row <= $sampleRows + 1; $row++) {
            $this->info("--- Row {$row} ---");
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $header = $headers[$colLetter] ?? "Col {$colLetter}";
                $value = $sheet->getCell($colLetter . $row)->getValue();

                if ($value !== null && $value !== '') {
                    $displayValue = mb_strlen((string)$value) > 100
                        ? mb_substr((string)$value, 0, 100) . '...'
                        : $value;
                    $this->line("  [{$colLetter}] {$header}: {$displayValue}");
                }
            }
            $this->newLine();
        }

        // Print summary of non-empty values per column
        $this->info('=== COLUMN FILL RATES ===');
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $nonEmpty = 0;
            for ($row = 2; $row <= $highestRow; $row++) {
                $value = $sheet->getCell($colLetter . $row)->getValue();
                if ($value !== null && trim((string)$value) !== '') {
                    $nonEmpty++;
                }
            }
            $header = $headers[$colLetter] ?? "Col {$colLetter}";
            $total = $highestRow - 1;
            $pct = $total > 0 ? round(($nonEmpty / $total) * 100) : 0;
            $this->line("  {$colLetter} ({$header}): {$nonEmpty}/{$total} ({$pct}%)");
        }

        // Print unique values for category column (if identifiable)
        $this->newLine();
        $this->info('=== UNIQUE CATEGORY VALUES (looking for category-like columns) ===');
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $header = mb_strtolower((string)($headers[$colLetter] ?? ''));
            if (str_contains($header, 'рубрик') || str_contains($header, 'категор') || str_contains($header, 'тип') || str_contains($header, 'вид')) {
                $this->info("Column {$colLetter} ({$headers[$colLetter]}) unique values:");
                $uniqueValues = [];
                for ($row = 2; $row <= $highestRow; $row++) {
                    $value = trim((string)($sheet->getCell($colLetter . $row)->getValue() ?? ''));
                    if ($value !== '') {
                        $uniqueValues[$value] = ($uniqueValues[$value] ?? 0) + 1;
                    }
                }
                arsort($uniqueValues);
                foreach ($uniqueValues as $val => $count) {
                    $this->line("    [{$count}x] {$val}");
                }
            }
        }

        // Print sample working hours values
        $this->newLine();
        $this->info('=== SAMPLE WORKING HOURS VALUES ===');
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $header = mb_strtolower((string)($headers[$colLetter] ?? ''));
            if (str_contains($header, 'режим') || str_contains($header, 'часы') || str_contains($header, 'график') || str_contains($header, 'работ')) {
                $this->info("Column {$colLetter} ({$headers[$colLetter]}) sample values:");
                $samples = 0;
                $uniqueFormats = [];
                for ($row = 2; $row <= $highestRow; $row++) {
                    $value = trim((string)($sheet->getCell($colLetter . $row)->getValue() ?? ''));
                    if ($value !== '') {
                        $uniqueFormats[$value] = ($uniqueFormats[$value] ?? 0) + 1;
                        if ($samples < 20) {
                            $this->line("    Row {$row}: {$value}");
                            $samples++;
                        }
                    }
                }
                $this->line("  Total unique formats: " . count($uniqueFormats));
            }
        }
    }
}
