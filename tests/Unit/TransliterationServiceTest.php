<?php

use App\Services\TransliterationService;

beforeEach(function () {
    $this->service = new TransliterationService();
});

test('transliterates pure Russian text', function () {
    expect($this->service->transliterate('Бочонок'))->toBe('Bochonok');
});

test('transliterates mixed Cyrillic and Latin text', function () {
    expect($this->service->transliterate('Guinness Pub Алматы'))->toBe('Guinness Pub Almaty');
});

test('transliterates Kazakh-specific characters', function () {
    expect($this->service->transliterate('Қабанбай Батыр көшесі'))
        ->toBe('Qabanbay Batyr koshesi');
});

test('transliterates Russian address', function () {
    expect($this->service->transliterate('ул. Жибек Жолы, 50'))
        ->toBe('ul. Zhibek Zholy, 50');
});

test('returns already-Latin string unchanged', function () {
    expect($this->service->transliterate('Guinness Pub'))->toBe('Guinness Pub');
});

test('handles empty string', function () {
    expect($this->service->transliterate(''))->toBe('');
});

test('preserves digits and punctuation', function () {
    expect($this->service->transliterate('Кафе №1, ул. Абая 25/3'))
        ->toBe('Kafe №1, ul. Abaya 25/3');
});

test('handles uppercase Cyrillic', function () {
    expect($this->service->transliterate('РЕСТОРАН'))->toBe('RESTORAN');
});

test('handles ё character', function () {
    expect($this->service->transliterate('Зелёная Долина'))->toBe('Zelyonaya Dolina');
});

test('handles щ character', function () {
    expect($this->service->transliterate('Борщ'))->toBe('Borshch');
});

test('handles ю and я characters', function () {
    expect($this->service->transliterate('Юля и Яна'))->toBe('Yulya i Yana');
});

test('omits hard and soft signs', function () {
    expect($this->service->transliterate('Объект подъезд'))->toBe('Obekt podezd');
});
