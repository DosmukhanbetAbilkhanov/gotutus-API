<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Place;
use App\Models\PlaceTranslation;
use Illuminate\Database\Seeder;

/**
 * One-time backfill: for every place that has a Russian translation but is
 * missing 'en' and/or 'kk', copy the Russian name/address/description into the
 * missing languages. Idempotent — existing translations are never overwritten.
 *
 * Run: php artisan db:seed --class=BackfillPlaceTranslationsSeeder
 */
class BackfillPlaceTranslationsSeeder extends Seeder
{
    public function run(): void
    {
        $created = 0;

        Place::with('translations')->chunkById(500, function ($places) use (&$created) {
            $insert = [];

            foreach ($places as $place) {
                $ru = $place->translations->firstWhere('language_code', 'ru');
                if ($ru === null) {
                    continue; // nothing to copy from
                }

                foreach (['en', 'kk'] as $lang) {
                    if ($place->translations->firstWhere('language_code', $lang) !== null) {
                        continue; // already present
                    }

                    $insert[] = [
                        'place_id' => $place->id,
                        'language_code' => $lang,
                        'name' => $ru->name,
                        'address' => $ru->address,
                        'description' => $ru->description,
                    ];
                    $created++;
                }
            }

            if ($insert !== []) {
                PlaceTranslation::insert($insert);
            }
        });

        $this->command?->info("Backfilled {$created} place translations (en/kk copied from ru).");
    }
}
