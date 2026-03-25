<?php

use App\Services\WorkingHoursParserService;

beforeEach(function () {
    $this->parser = new WorkingHoursParserService();
});

test('parses 24/7 format (Круглосуточно)', function () {
    $result = $this->parser->parse('Круглосуточно');

    expect($result)->toHaveCount(7);
    foreach ($result as $day) {
        expect($day['open_time'])->toBe('00:00');
        expect($day['close_time'])->toBe('23:59');
    }
});

test('parses daily format (Ежедневно)', function () {
    $result = $this->parser->parse('Ежедневно с 11:00 до 02:00');

    expect($result)->toHaveCount(7);
    foreach ($result as $day) {
        expect($day['open_time'])->toBe('11:00');
        expect($day['close_time'])->toBe('02:00');
    }
});

test('parses individual day schedule', function () {
    $raw = 'Пн: с 10:00 до 24:00, Вт: с 10:00 до 24:00, Ср: с 10:00 до 24:00, Чт: с 10:00 до 24:00, Пт: с 10:00 до 01:00, Сб: с 10:00 до 01:00, Вс: с 10:00 до 24:00';
    $result = $this->parser->parse($raw);

    expect($result)->toHaveCount(7);
    // Monday
    expect($result[0]['day_of_week'])->toBe(0);
    expect($result[0]['open_time'])->toBe('10:00');
    expect($result[0]['close_time'])->toBe('24:00');
    // Friday
    expect($result[4]['day_of_week'])->toBe(4);
    expect($result[4]['close_time'])->toBe('01:00');
});

test('parses weekday/weekend with выходной', function () {
    $raw = 'Пн: с 09:00 до 18:00, Вт: с 09:00 до 18:00, Ср: с 09:00 до 18:00, Чт: с 09:00 до 18:00, Пт: с 09:00 до 18:00, Сб: выходной, Вс: выходной';
    $result = $this->parser->parse($raw);

    expect($result)->toHaveCount(7);
    // Monday-Friday should have times
    for ($i = 0; $i < 5; $i++) {
        expect($result[$i]['open_time'])->toBe('09:00');
        expect($result[$i]['close_time'])->toBe('18:00');
    }
    // Saturday-Sunday should be closed
    expect($result[5]['open_time'])->toBeNull();
    expect($result[5]['close_time'])->toBeNull();
    expect($result[6]['open_time'])->toBeNull();
    expect($result[6]['close_time'])->toBeNull();
});

test('parses day range format (Пн-Пт)', function () {
    $raw = 'Пн-Пт: 09:00-18:00, Сб-Вс: 10:00-16:00';
    $result = $this->parser->parse($raw);

    expect($result)->toHaveCount(7);
    for ($i = 0; $i < 5; $i++) {
        expect($result[$i]['open_time'])->toBe('09:00');
        expect($result[$i]['close_time'])->toBe('18:00');
    }
    expect($result[5]['open_time'])->toBe('10:00');
    expect($result[6]['close_time'])->toBe('16:00');
});

test('parses day range with выходной', function () {
    $raw = 'Пн-Пт: 09:00-22:00, Сб: 10:00-23:00, Вс: выходной';
    $result = $this->parser->parse($raw);

    expect($result)->toHaveCount(7);
    for ($i = 0; $i < 5; $i++) {
        expect($result[$i]['open_time'])->toBe('09:00');
    }
    expect($result[5]['open_time'])->toBe('10:00');
    expect($result[5]['close_time'])->toBe('23:00');
    expect($result[6]['open_time'])->toBeNull();
    expect($result[6]['close_time'])->toBeNull();
});

test('strips parenthetical annotations', function () {
    $raw = 'Ежедневно с 10:00 до 24:00 (служба доставки: пн-вс 10:30-23:00)';
    $result = $this->parser->parse($raw);

    expect($result)->toHaveCount(7);
    foreach ($result as $day) {
        expect($day['open_time'])->toBe('10:00');
        expect($day['close_time'])->toBe('24:00');
    }
});

test('normalizes single-digit hours', function () {
    $result = $this->parser->parse('Ежедневно с 9:00 до 2:00');

    expect($result[0]['open_time'])->toBe('09:00');
    expect($result[0]['close_time'])->toBe('02:00');
});

test('handles null input', function () {
    $result = $this->parser->parse(null);

    expect($result)->toHaveCount(7);
    foreach ($result as $day) {
        expect($day['open_time'])->toBeNull();
        expect($day['close_time'])->toBeNull();
    }
});

test('handles empty string input', function () {
    $result = $this->parser->parse('');

    expect($result)->toHaveCount(7);
    foreach ($result as $day) {
        expect($day['open_time'])->toBeNull();
    }
});

test('handles garbage input gracefully', function () {
    $result = $this->parser->parse('some random text that is not working hours');

    expect($result)->toHaveCount(7);
    foreach ($result as $day) {
        expect($day['open_time'])->toBeNull();
    }
});

test('each entry has correct day_of_week', function () {
    $result = $this->parser->parse('Круглосуточно');

    for ($i = 0; $i < 7; $i++) {
        expect($result[$i]['day_of_week'])->toBe($i);
    }
});

test('parses 24/7 with tech break annotation stripped', function () {
    $raw = 'Круглосуточно (технический перерыв: пн-вс 5:00-6:00)';
    $result = $this->parser->parse($raw);

    expect($result)->toHaveCount(7);
    foreach ($result as $day) {
        expect($day['open_time'])->toBe('00:00');
        expect($day['close_time'])->toBe('23:59');
    }
});
