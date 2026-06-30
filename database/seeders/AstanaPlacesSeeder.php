<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ActivityType;
use App\Models\Place;
use App\Models\PlaceTranslation;
use App\Models\PlaceWorkingHour;
use App\Services\WorkingHoursParserService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds Astana places from the 2GIS export (database/data/astana_places.json).
 *
 * - Activity types are derived from the 2GIS category text (column E).
 * - Working hours (column G) are normalized via WorkingHoursParserService.
 * - Translations are stored in Russian only (app falls back to ru for kk/en).
 * - Idempotent: a place is matched by Russian name + address within Astana, so
 *   re-running skips existing rows (different branches of a chain differ by address).
 *
 * Run: php artisan db:seed --class=AstanaPlacesSeeder
 */
class AstanaPlacesSeeder extends Seeder
{
    /**
     * Category token (substring, lowercased) → activity slug, in priority order.
     * Each comma/slash-separated token in column E maps to the FIRST rule it
     * matches; a place collects the union across all its tokens.
     *
     * @var array<int, array{0: string, 1: string}>
     */
    private const RULES = [
        ['суши', 'sushi'],
        ['кофейн', 'coffee'], ['продажа кофе', 'coffee'], ['кофе', 'coffee'],
        ['быстрое питание', 'fast_food'], ['пиццери', 'fast_food'], ['фаст', 'fast_food'],
        ['столов', 'fast_food'], ['бургер', 'fast_food'], ['шаурм', 'fast_food'], ['донер', 'fast_food'],
        ['кафе', 'restaurant'], ['ресторан', 'restaurant'], ['банкетн', 'restaurant'],
        ['кальян', 'hookah'],
        ['бар', 'beer'], ['паб', 'beer'], ['пивн', 'beer'], ['разливное', 'beer'], ['алкогол', 'beer'], ['ночн', 'beer'],
        ['саун', 'bathhouse'], ['бани', 'bathhouse'], ['баня', 'bathhouse'], ['банн', 'bathhouse'],
        ['spa', 'bathhouse'], ['хаммам', 'bathhouse'], ['хамам', 'bathhouse'],
        ['караоке', 'karaoke'],
        ['бильярд', 'billiards'],
        ['кинотеатр', 'cinema'], ['киноаттракцион', 'cinema'], ['кино', 'cinema'],
        ['кымыз', 'kumys'], ['кумыс', 'kumys'],
        ['пейнтбол', 'paintball'], ['страйкбол', 'paintball'], ['лазертаг', 'paintball'], ['стрелков', 'paintball'],
        ['боулинг', 'bowling'],
        ['концерт', 'concert'],
        ['стендап', 'standup'],
        ['квиз', 'quiz'],
        ['квест', 'kvest'],
        ['видеоигр', 'pc_club'], ['компьютерн', 'pc_club'], ['киберспорт', 'pc_club'],
        ['теннис', 'tennis'], ['настольн', 'board-games'],
        ['футбол', 'football'],
    ];

    public function run(WorkingHoursParserService $hoursParser): void
    {
        $cityId = $this->resolveAstanaCityId();
        if ($cityId === null) {
            $this->command?->error('Astana city not found — run ProductionSeeder first.');

            return;
        }

        $path = database_path('data/astana_places.json');
        if (! is_file($path)) {
            $this->command?->error("Data file missing: {$path}");

            return;
        }

        /** @var array<int, array<string, mixed>> $rows */
        $rows = json_decode((string) file_get_contents($path), true) ?: [];

        DB::connection()->disableQueryLog();

        $stats = $this->import($rows, $cityId, $hoursParser, function (int $n) {
            $this->command?->info("  …{$n} places imported");
        });

        $this->command?->info("Astana places: {$stats['imported']} imported, {$stats['skipped']} skipped (existing/empty), {$stats['noActivity']} had no matching activity.");
    }

    /**
     * Import the given rows into a city. Idempotent (by Russian name+address).
     * Public + dependency-light so it can be unit-tested with a small fixture.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @param  callable(int):void|null  $progress  called every 500 imports with the running count
     * @return array{imported: int, skipped: int, noActivity: int}
     */
    public function import(array $rows, int $cityId, WorkingHoursParserService $hoursParser, ?callable $progress = null): array
    {
        // slug => id
        $activityIds = ActivityType::pluck('id', 'slug')->all();

        // Existing "name\x1Faddress" keys in Astana (idempotency).
        $existing = $this->existingKeys($cityId);

        $imported = 0;
        $skipped = 0;
        $noActivity = 0;
        $now = now();

        foreach ($rows as $r) {
            $name = trim((string) ($r['name'] ?? ''));
            if ($name === '') {
                $skipped++;
                continue;
            }

            $address = isset($r['address']) ? trim((string) $r['address']) : null;
            $key = $name."\x1F".($address ?? '');
            if (isset($existing[$key])) {
                $skipped++;
                continue;
            }

            // Map category → activity ids.
            $slugs = $this->mapActivities((string) ($r['category'] ?? ''));
            $ids = array_values(array_filter(array_map(
                fn (string $s) => $activityIds[$s] ?? null,
                $slugs,
            )));
            if (empty($ids)) {
                $noActivity++;
                $existing[$key] = true; // don't reconsider on re-run
                continue;
            }

            DB::transaction(function () use ($r, $cityId, $name, $address, $ids, $hoursParser, $now) {
                $place = Place::create([
                    'city_id' => $cityId,
                    'latitude' => $r['latitude'] ?? null,
                    'longitude' => $r['longitude'] ?? null,
                    'phone' => $this->cleanPhone($r['phone'] ?? null),
                    'website' => $this->cleanText($r['website'] ?? null),
                    'instagram' => $this->cleanInstagram($r['instagram'] ?? null),
                ]);

                PlaceTranslation::create([
                    'place_id' => $place->id,
                    'language_code' => 'ru',
                    'name' => $name,
                    'address' => $address,
                ]);

                $place->activityTypes()->attach($ids);

                $hourRows = [];
                foreach ($hoursParser->parse($this->cleanText($r['hours'] ?? null)) as $d) {
                    if ($d['open_time'] !== null && $d['close_time'] !== null) {
                        $hourRows[] = [
                            'place_id' => $place->id,
                            'day_of_week' => $d['day_of_week'],
                            'open_time' => $d['open_time'],
                            'close_time' => $d['close_time'],
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }
                if ($hourRows !== []) {
                    PlaceWorkingHour::insert($hourRows);
                }
            });

            $existing[$key] = true;
            $imported++;

            if ($progress !== null && $imported % 500 === 0) {
                $progress($imported);
            }
        }

        return ['imported' => $imported, 'skipped' => $skipped, 'noActivity' => $noActivity];
    }

    private function resolveAstanaCityId(): ?int
    {
        return DB::table('city_translations')
            ->where('language_code', 'en')
            ->where('name', 'Astana')
            ->value('city_id');
    }

    /**
     * @return array<string, true>
     */
    private function existingKeys(int $cityId): array
    {
        $rows = DB::table('place_translations as pt')
            ->join('places as p', 'p.id', '=', 'pt.place_id')
            ->where('p.city_id', $cityId)
            ->where('pt.language_code', 'ru')
            ->get(['pt.name', 'pt.address']);

        $keys = [];
        foreach ($rows as $row) {
            $keys[trim($row->name)."\x1F".trim((string) $row->address)] = true;
        }

        return $keys;
    }

    /**
     * Map a 2GIS category string to a set of activity slugs.
     *
     * @return array<int, string>
     */
    public function mapActivities(string $category): array
    {
        $slugs = [];
        foreach (preg_split('#[,/]#u', $category) as $part) {
            $token = mb_strtolower(trim((string) $part));
            if ($token === '') {
                continue;
            }
            foreach (self::RULES as [$needle, $slug]) {
                if (str_contains($token, $needle)) {
                    $slugs[$slug] = true;
                    break; // first match wins for this token
                }
            }
        }

        return array_keys($slugs);
    }

    private function cleanText(mixed $v): ?string
    {
        if ($v === null) {
            return null;
        }
        $s = trim((string) $v);

        return $s === '' ? null : $s;
    }

    private function cleanPhone(mixed $v): ?string
    {
        $s = $this->cleanText($v);
        if ($s === null) {
            return null;
        }
        // Keep the first number only.
        return trim(explode(',', $s)[0]) ?: null;
    }

    private function cleanInstagram(mixed $v): ?string
    {
        $s = $this->cleanText($v);
        if ($s === null) {
            return null;
        }
        $s = preg_replace('#^https?://(www\.)?instagram\.com/#i', '', $s);

        return rtrim((string) $s, '/') ?: null;
    }
}
