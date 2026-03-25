<?php

declare(strict_types=1);

namespace App\DTOs;

class PlaceImportResult
{
    public function __construct(
        public readonly int $totalRows,
        public readonly int $imported,
        public readonly int $skipped,
        public readonly int $failed,
        public readonly array $errors,
        public readonly array $warnings,
    ) {}

    public function toArray(): array
    {
        return [
            'total_rows' => $this->totalRows,
            'imported' => $this->imported,
            'skipped' => $this->skipped,
            'failed' => $this->failed,
            'errors' => $this->errors,
            'warnings' => $this->warnings,
        ];
    }
}
