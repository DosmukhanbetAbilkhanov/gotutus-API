<?php

use App\Services\PlaceImportService;
use App\Services\TransliterationService;
use App\Services\WorkingHoursParserService;

beforeEach(function () {
    // Use reflection to test private methods
    $this->service = new PlaceImportService(
        new TransliterationService(),
        new WorkingHoursParserService(),
    );
});

test('phone resolution prioritizes mobile phone', function () {
    $method = new ReflectionMethod(PlaceImportService::class, 'resolvePhone');
    $method->setAccessible(true);

    $result = $method->invoke($this->service, '+77021234567', '+71234567', '+77029876543');
    expect($result)->toBe('+77021234567');
});

test('phone resolution falls back to landline', function () {
    $method = new ReflectionMethod(PlaceImportService::class, 'resolvePhone');
    $method->setAccessible(true);

    $result = $method->invoke($this->service, null, '+71234567', '+77029876543');
    expect($result)->toBe('+71234567');
});

test('phone resolution falls back to whatsapp', function () {
    $method = new ReflectionMethod(PlaceImportService::class, 'resolvePhone');
    $method->setAccessible(true);

    $result = $method->invoke($this->service, null, null, '+77029876543');
    expect($result)->toBe('+77029876543');
});

test('phone resolution returns null when all empty', function () {
    $method = new ReflectionMethod(PlaceImportService::class, 'resolvePhone');
    $method->setAccessible(true);

    $result = $method->invoke($this->service, null, '', '-');
    expect($result)->toBeNull();
});

test('clean phone strips non-standard dashes and spaces', function () {
    $method = new ReflectionMethod(PlaceImportService::class, 'cleanPhone');
    $method->setAccessible(true);

    // The actual file uses U+2012 (figure dash): +7‒702‒350‒16‒69
    $result = $method->invoke($this->service, "+7\u{2012}702\u{2012}350\u{2012}16\u{2012}69");
    expect($result)->toBe('+77023501669');
});

test('clean phone handles standard formats', function () {
    $method = new ReflectionMethod(PlaceImportService::class, 'cleanPhone');
    $method->setAccessible(true);

    expect($method->invoke($this->service, '+7 (702) 350-16-69'))->toBe('+77023501669');
    expect($method->invoke($this->service, '87023501669'))->toBe('87023501669');
    expect($method->invoke($this->service, '-'))->toBeNull();
    expect($method->invoke($this->service, ''))->toBeNull();
    expect($method->invoke($this->service, null))->toBeNull();
});

test('subcategory mapping resolves known categories', function () {
    $method = new ReflectionMethod(PlaceImportService::class, 'resolveActivityTypeSlugs');
    $method->setAccessible(true);

    expect($method->invoke($this->service, 'Бары, Караоке-залы'))
        ->toContain('beer')
        ->toContain('karaoke');

    expect($method->invoke($this->service, 'Бани / Сауны'))
        ->toContain('bathhouse');

    expect($method->invoke($this->service, 'Кофейни'))
        ->toContain('coffee');

    expect($method->invoke($this->service, 'Быстрое питание, Доставка еды'))
        ->toContain('fast_food')
        ->toHaveCount(1); // Both map to fast_food, should be deduplicated

    expect($method->invoke($this->service, 'Клубы видеоигр, Компьютерные клубы'))
        ->toContain('pc_club')
        ->toHaveCount(1);
});

test('subcategory mapping returns empty for null', function () {
    $method = new ReflectionMethod(PlaceImportService::class, 'resolveActivityTypeSlugs');
    $method->setAccessible(true);

    expect($method->invoke($this->service, null))->toBe([]);
    expect($method->invoke($this->service, ''))->toBe([]);
});

test('subcategory mapping handles single values', function () {
    $method = new ReflectionMethod(PlaceImportService::class, 'resolveActivityTypeSlugs');
    $method->setAccessible(true);

    expect($method->invoke($this->service, 'Столовые'))->toContain('fast_food');
    expect($method->invoke($this->service, 'Кинотеатры'))->toContain('cinema');
    expect($method->invoke($this->service, 'Бильярдные залы'))->toContain('billiards');
    expect($method->invoke($this->service, 'Суши-бары'))->toContain('sushi');
});
