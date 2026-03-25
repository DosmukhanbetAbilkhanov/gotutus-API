<?php

declare(strict_types=1);

namespace App\Services;

class WorkingHoursParserService
{
    /** Russian day abbreviations to day_of_week index (0=Mon..6=Sun). */
    private const DAY_MAP = [
        'Пн' => 0,
        'пн' => 0,
        'Вт' => 1,
        'вт' => 1,
        'Ср' => 2,
        'ср' => 2,
        'Чт' => 3,
        'чт' => 3,
        'Пт' => 4,
        'пт' => 4,
        'Сб' => 5,
        'сб' => 5,
        'Вс' => 6,
        'вс' => 6,
    ];

    /**
     * Parse Russian working hours string into structured array.
     *
     * @param  string|null  $rawHours  Raw working hours text from Excel
     * @return array<int, array{day_of_week: int, open_time: ?string, close_time: ?string}>
     *         Returns array of 7 entries (Mon-Sun), null times = closed
     */
    public function parse(?string $rawHours): array
    {
        if ($rawHours === null || trim($rawHours) === '') {
            return $this->allDaysClosed();
        }

        $rawHours = trim($rawHours);

        // Strip parenthetical annotations like "(служба доставки: ...)" or "(касса: ...)"
        $cleaned = preg_replace('/\s*\([^)]*\)\s*/', '', $rawHours);
        $cleaned = trim($cleaned);

        // Try each parser in order of specificity
        $result = $this->parseRoundTheClock($cleaned)
            ?? $this->parseDaily($cleaned)
            ?? $this->parseIndividualDays($cleaned)
            ?? null;

        if ($result !== null) {
            return $result;
        }

        if (app()->bound('log')) {
            app('log')->warning("WorkingHoursParser: unable to parse working hours", ['raw' => $rawHours]);
        }

        return $this->allDaysClosed();
    }

    /**
     * Parse "Круглосуточно" (24/7) format.
     */
    private function parseRoundTheClock(string $text): ?array
    {
        if (!str_starts_with(mb_strtolower($text), 'круглосуточно')) {
            return null;
        }

        return $this->fillAllDays('00:00', '23:59');
    }

    /**
     * Parse "Ежедневно с HH:MM до HH:MM" format.
     */
    private function parseDaily(string $text): ?array
    {
        if (!str_starts_with(mb_strtolower($text), 'ежедневно')) {
            return null;
        }

        // Match "с HH:MM до HH:MM" or "HH:MM-HH:MM"
        if (preg_match('/с\s+(\d{1,2}:\d{2})\s+до\s+(\d{1,2}:\d{2})/u', $text, $matches)) {
            return $this->fillAllDays(
                $this->normalizeTime($matches[1]),
                $this->normalizeTime($matches[2])
            );
        }

        if (preg_match('/(\d{1,2}:\d{2})\s*[-–]\s*(\d{1,2}:\d{2})/u', $text, $matches)) {
            return $this->fillAllDays(
                $this->normalizeTime($matches[1]),
                $this->normalizeTime($matches[2])
            );
        }

        return null;
    }

    /**
     * Parse individual day schedules like "Пн: с 10:00 до 22:00, Вт: ..." or "Пн-Пт: 09:00-18:00".
     */
    private function parseIndividualDays(string $text): ?array
    {
        $result = $this->allDaysClosed();
        $matched = false;

        // Split by comma to get segments
        $segments = preg_split('/,\s*/u', $text);

        foreach ($segments as $segment) {
            $segment = trim($segment);
            if ($segment === '') {
                continue;
            }

            // Try day range format: "Пн-Пт: ..." or "Пн-Пт 09:00-18:00"
            if (preg_match('/^(\p{L}{2})\s*[-–]\s*(\p{L}{2})\s*:?\s*(.+)$/u', $segment, $rangeMatch)) {
                $startDay = self::DAY_MAP[$rangeMatch[1]] ?? null;
                $endDay = self::DAY_MAP[$rangeMatch[2]] ?? null;
                $timePart = trim($rangeMatch[3]);

                if ($startDay !== null && $endDay !== null) {
                    $timeData = $this->parseTimePart($timePart);
                    $days = $this->expandDayRange($startDay, $endDay);
                    foreach ($days as $day) {
                        $result[$day] = $timeData + ['day_of_week' => $day];
                    }
                    $matched = true;

                    continue;
                }
            }

            // Try single day format: "Пн: с 10:00 до 22:00" or "Пн: выходной"
            if (preg_match('/^(\p{L}{2})\s*:\s*(.+)$/u', $segment, $dayMatch)) {
                $dayIndex = self::DAY_MAP[$dayMatch[1]] ?? null;
                $timePart = trim($dayMatch[2]);

                if ($dayIndex !== null) {
                    $timeData = $this->parseTimePart($timePart);
                    $result[$dayIndex] = $timeData + ['day_of_week' => $dayIndex];
                    $matched = true;
                }
            }
        }

        return $matched ? array_values($result) : null;
    }

    /**
     * Parse the time part of a segment (e.g., "с 10:00 до 22:00", "10:00-22:00", "выходной").
     *
     * @return array{open_time: ?string, close_time: ?string}
     */
    private function parseTimePart(string $timePart): array
    {
        $lower = mb_strtolower($timePart);

        if (str_contains($lower, 'выходной') || str_contains($lower, 'закрыт')) {
            return ['open_time' => null, 'close_time' => null];
        }

        // "с HH:MM до HH:MM"
        if (preg_match('/с\s+(\d{1,2}:\d{2})\s+до\s+(\d{1,2}:\d{2})/u', $timePart, $m)) {
            return [
                'open_time' => $this->normalizeTime($m[1]),
                'close_time' => $this->normalizeTime($m[2]),
            ];
        }

        // "HH:MM-HH:MM"
        if (preg_match('/(\d{1,2}:\d{2})\s*[-–]\s*(\d{1,2}:\d{2})/u', $timePart, $m)) {
            return [
                'open_time' => $this->normalizeTime($m[1]),
                'close_time' => $this->normalizeTime($m[2]),
            ];
        }

        return ['open_time' => null, 'close_time' => null];
    }

    /**
     * Normalize time format: "5:00" -> "05:00", "23:00" stays "23:00".
     */
    private function normalizeTime(string $time): string
    {
        $parts = explode(':', $time);
        if (count($parts) !== 2) {
            return $time;
        }

        return sprintf('%02d:%s', (int) $parts[0], $parts[1]);
    }

    /**
     * Expand a day range (e.g., Mon-Fri = [0,1,2,3,4]).
     *
     * @return int[]
     */
    private function expandDayRange(int $start, int $end): array
    {
        $days = [];
        $current = $start;
        while (true) {
            $days[] = $current;
            if ($current === $end) {
                break;
            }
            $current = ($current + 1) % 7;
        }

        return $days;
    }

    /**
     * Fill all 7 days with the same time.
     */
    private function fillAllDays(string $openTime, string $closeTime): array
    {
        $result = [];
        for ($i = 0; $i < 7; $i++) {
            $result[] = [
                'day_of_week' => $i,
                'open_time' => $openTime,
                'close_time' => $closeTime,
            ];
        }

        return $result;
    }

    /**
     * Return 7 entries with null times (all days closed/unknown).
     */
    private function allDaysClosed(): array
    {
        $result = [];
        for ($i = 0; $i < 7; $i++) {
            $result[] = [
                'day_of_week' => $i,
                'open_time' => null,
                'close_time' => null,
            ];
        }

        return $result;
    }
}
