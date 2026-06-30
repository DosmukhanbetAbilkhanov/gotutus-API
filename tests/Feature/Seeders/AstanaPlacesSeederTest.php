<?php

use App\Models\ActivityType;
use App\Models\City;
use App\Models\Place;
use App\Services\WorkingHoursParserService;
use Database\Seeders\AstanaPlacesSeeder;

beforeEach(function () {
    // Astana must be resolvable by its English translation name.
    $this->city = City::factory()->create();
    $this->city->translations()->where('language_code', 'en')->delete();
    $this->city->translations()->create(['language_code' => 'en', 'name' => 'Astana']);

    // Activity types referenced by the fixtures (real slugs).
    foreach (['fast_food', 'restaurant', 'sushi', 'beer', 'bathhouse', 'coffee'] as $slug) {
        ActivityType::factory()->create(['slug' => $slug]);
    }

    $this->seeder = new AstanaPlacesSeeder();
    $this->parser = app(WorkingHoursParserService::class);
});

describe('mapActivities', function () {
    it('maps a multi-category row to the union of activities', function () {
        $slugs = $this->seeder->mapActivities('Быстрое питание, Доставка еды, Кафе, Пиццерии, Рестораны, Суши-бары');
        expect($slugs)->toEqualCanonicalizing(['fast_food', 'restaurant', 'sushi']);
    });

    it('maps "Суши-бары" to sushi, not beer (rule order)', function () {
        expect($this->seeder->mapActivities('Суши-бары'))->toBe(['sushi']);
    });

    it('maps кафе → restaurant and кофейни → coffee separately', function () {
        expect($this->seeder->mapActivities('Кафе'))->toBe(['restaurant']);
        expect($this->seeder->mapActivities('Кофейни'))->toBe(['coffee']);
    });

    it('returns empty for non-hangout categories', function () {
        expect($this->seeder->mapActivities('Стриптиз-клубы'))->toBe([]);
    });
});

describe('import', function () {
    $okadzaki = [
        'name' => 'Okadzaki.kz, кафе',
        'address' => 'проспект Улы Дала, 41а',
        'website' => 'http://okadzaki.kz',
        'category' => 'Быстрое питание, Доставка еды, Кафе, Пиццерии, Рестораны, Суши-бары',
        'hours' => 'Ежедневно с 09:00 до 01:00',
        'phone' => '+77773333333, +77019999999',
        'instagram' => 'https://instagram.com/okadzaki.kz',
        'latitude' => 51.097749,
        'longitude' => 71.414479,
    ];

    it('creates a place with mapped activities, hours, and cleaned fields', function () use ($okadzaki) {
        $stats = $this->seeder->import([$okadzaki], $this->city->id, $this->parser);

        expect($stats['imported'])->toBe(1);

        $place = Place::with(['activityTypes', 'workingHours', 'translations'])->first();
        expect($place->city_id)->toBe($this->city->id)
            ->and((float) $place->latitude)->toBe(51.097749)
            ->and($place->phone)->toBe('+77773333333')           // first number only
            ->and($place->instagram)->toBe('okadzaki.kz');        // url stripped to handle

        expect($place->activityTypes->pluck('slug')->all())
            ->toEqualCanonicalizing(['fast_food', 'restaurant', 'sushi']);

        // "Ежедневно" → all 7 days, 09:00–01:00
        expect($place->workingHours)->toHaveCount(7);
        expect($place->workingHours->firstWhere('day_of_week', 0)->open_time)->toBe('09:00');
        expect($place->workingHours->firstWhere('day_of_week', 0)->close_time)->toBe('01:00');

        // Russian-only translation
        expect($place->translations->pluck('language_code')->all())->toBe(['ru']);
        expect($place->translations->first()->name)->toBe('Okadzaki.kz, кафе');
    });

    it('parses round-the-clock hours', function () {
        $this->seeder->import([[
            'name' => '777, сауна',
            'address' => 'улица Ыкылас Дукенулы, 29',
            'category' => 'Бани / Сауны',
            'hours' => 'Круглосуточно',
            'latitude' => 51.18, 'longitude' => 71.43,
        ]], $this->city->id, $this->parser);

        $place = Place::with('workingHours')->first();
        expect($place->workingHours)->toHaveCount(7);
        expect($place->workingHours->first()->open_time)->toBe('00:00');
        expect($place->workingHours->first()->close_time)->toBe('23:59');
    });

    it('is idempotent — re-importing skips existing places', function () use ($okadzaki) {
        $this->seeder->import([$okadzaki], $this->city->id, $this->parser);
        $stats = $this->seeder->import([$okadzaki], $this->city->id, $this->parser);

        expect($stats['imported'])->toBe(0)
            ->and($stats['skipped'])->toBe(1)
            ->and(Place::count())->toBe(1);
    });

    it('keeps different branches of a chain (same name, different address)', function () {
        $rows = [
            ['name' => 'Coffee BOOM', 'address' => 'ул. A, 1', 'category' => 'Кофейни', 'hours' => null, 'latitude' => 51.1, 'longitude' => 71.4],
            ['name' => 'Coffee BOOM', 'address' => 'ул. B, 2', 'category' => 'Кофейни', 'hours' => null, 'latitude' => 51.2, 'longitude' => 71.5],
        ];
        $stats = $this->seeder->import($rows, $this->city->id, $this->parser);
        expect($stats['imported'])->toBe(2)->and(Place::count())->toBe(2);
    });

    it('skips rows with no matching activity', function () {
        $stats = $this->seeder->import([[
            'name' => 'Some Club', 'address' => 'x', 'category' => 'Стриптиз-клубы', 'hours' => null,
        ]], $this->city->id, $this->parser);
        expect($stats['noActivity'])->toBe(1)->and(Place::count())->toBe(0);
    });
});
