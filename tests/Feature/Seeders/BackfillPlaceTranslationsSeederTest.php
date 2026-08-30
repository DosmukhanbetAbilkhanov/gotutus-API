<?php

use App\Models\City;
use App\Models\Place;
use App\Models\PlaceTranslation;
use Database\Seeders\BackfillPlaceTranslationsSeeder;

beforeEach(function () {
    $this->city = City::factory()->create();
});

function ruOnlyPlace(int $cityId, string $name, ?string $address = 'ул. A, 1'): Place
{
    $place = Place::factory()->create(['city_id' => $cityId]);
    // Factory may add its own translations — reset to ru-only for the test.
    $place->translations()->delete();
    $place->translations()->create([
        'language_code' => 'ru', 'name' => $name, 'address' => $address, 'description' => 'опис',
    ]);

    return $place;
}

it('copies ru into missing en and kk translations', function () {
    $place = ruOnlyPlace($this->city->id, 'Кафе Тест');

    (new BackfillPlaceTranslationsSeeder())->run();

    $place->load('translations');
    expect($place->translations->pluck('language_code')->all())->toEqualCanonicalizing(['ru', 'en', 'kk']);
    foreach (['en', 'kk'] as $lang) {
        $t = $place->translations->firstWhere('language_code', $lang);
        expect($t->name)->toBe('Кафе Тест')
            ->and($t->address)->toBe('ул. A, 1')
            ->and($t->description)->toBe('опис');
    }
});

it('is idempotent — does not duplicate on re-run', function () {
    ruOnlyPlace($this->city->id, 'Кафе Тест');

    (new BackfillPlaceTranslationsSeeder())->run();
    (new BackfillPlaceTranslationsSeeder())->run();

    expect(PlaceTranslation::count())->toBe(3);
});

it('does not overwrite existing en/kk translations', function () {
    $place = ruOnlyPlace($this->city->id, 'Кафе Тест');
    $place->translations()->create(['language_code' => 'en', 'name' => 'Custom EN', 'address' => 'St A']);

    (new BackfillPlaceTranslationsSeeder())->run();

    $place->load('translations');
    expect($place->translations->firstWhere('language_code', 'en')->name)->toBe('Custom EN')
        ->and($place->translations->firstWhere('language_code', 'kk')->name)->toBe('Кафе Тест');
});

it('skips places that have no ru translation', function () {
    $place = Place::factory()->create(['city_id' => $this->city->id]);
    $place->translations()->delete();
    $place->translations()->create(['language_code' => 'en', 'name' => 'Only EN']);

    (new BackfillPlaceTranslationsSeeder())->run();

    expect($place->fresh()->translations()->count())->toBe(1);
});
