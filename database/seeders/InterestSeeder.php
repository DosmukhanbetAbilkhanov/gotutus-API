<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InterestSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $interests = [
            ['slug' => 'beer', 'icon' => "\u{1F37A}", 'en' => 'Beer', 'ru' => 'Пиво', 'kk' => 'Сыра'],
            ['slug' => 'coffee', 'icon' => "\u{2615}", 'en' => 'Coffee', 'ru' => 'Кофе', 'kk' => 'Кофе'],
            ['slug' => 'sports', 'icon' => "\u{26BD}", 'en' => 'Sports', 'ru' => 'Спорт', 'kk' => 'Спорт'],
            ['slug' => 'fitness', 'icon' => "\u{1F4AA}", 'en' => 'Fitness', 'ru' => 'Фитнес', 'kk' => 'Фитнес'],
            ['slug' => 'music', 'icon' => "\u{1F3B5}", 'en' => 'Music', 'ru' => 'Музыка', 'kk' => 'Музыка'],
            ['slug' => 'cinema', 'icon' => "\u{1F3AC}", 'en' => 'Cinema', 'ru' => 'Кино', 'kk' => 'Кино'],
            ['slug' => 'travel', 'icon' => "\u{2708}\u{FE0F}", 'en' => 'Travel', 'ru' => 'Путешествия', 'kk' => 'Саяхат'],
            ['slug' => 'cooking', 'icon' => "\u{1F373}", 'en' => 'Cooking', 'ru' => 'Кулинария', 'kk' => 'Аспаздық'],
            ['slug' => 'gaming', 'icon' => "\u{1F3AE}", 'en' => 'Gaming', 'ru' => 'Видеоигры', 'kk' => 'Ойындар'],
            ['slug' => 'board-games', 'icon' => "\u{1F3B2}", 'en' => 'Board Games', 'ru' => 'Настолки', 'kk' => 'Үстел ойындары'],
            ['slug' => 'books', 'icon' => "\u{1F4DA}", 'en' => 'Books', 'ru' => 'Книги', 'kk' => 'Кітаптар'],
            ['slug' => 'art', 'icon' => "\u{1F3A8}", 'en' => 'Art', 'ru' => 'Искусство', 'kk' => 'Өнер'],
            ['slug' => 'photography', 'icon' => "\u{1F4F7}", 'en' => 'Photography', 'ru' => 'Фотография', 'kk' => 'Фотография'],
            ['slug' => 'nature', 'icon' => "\u{1F333}", 'en' => 'Nature', 'ru' => 'Природа', 'kk' => 'Табиғат'],
            ['slug' => 'technology', 'icon' => "\u{1F4BB}", 'en' => 'Technology', 'ru' => 'Технологии', 'kk' => 'Технологиялар'],
            ['slug' => 'politics', 'icon' => "\u{1F3DB}\u{FE0F}", 'en' => 'Politics', 'ru' => 'Политика', 'kk' => 'Саясат'],
            ['slug' => 'hookah', 'icon' => "\u{1F4A8}", 'en' => 'Hookah', 'ru' => 'Кальян', 'kk' => 'Кальян'],
            ['slug' => 'karaoke', 'icon' => "\u{1F3A4}", 'en' => 'Karaoke', 'ru' => 'Караоке', 'kk' => 'Караоке'],
            ['slug' => 'bowling', 'icon' => "\u{1F3B3}", 'en' => 'Bowling', 'ru' => 'Боулинг', 'kk' => 'Боулинг'],
            ['slug' => 'dancing', 'icon' => "\u{1F483}", 'en' => 'Dancing', 'ru' => 'Танцы', 'kk' => 'Би'],
            ['slug' => 'yoga', 'icon' => "\u{1F9D8}", 'en' => 'Yoga', 'ru' => 'Йога', 'kk' => 'Йога'],
            ['slug' => 'cars', 'icon' => "\u{1F697}", 'en' => 'Cars', 'ru' => 'Автомобили', 'kk' => 'Автокөліктер'],
            ['slug' => 'fashion', 'icon' => "\u{1F457}", 'en' => 'Fashion', 'ru' => 'Мода', 'kk' => 'Сән'],
            ['slug' => 'languages', 'icon' => "\u{1F30D}", 'en' => 'Languages', 'ru' => 'Языки', 'kk' => 'Тілдер'],
            ['slug' => 'startups', 'icon' => "\u{1F680}", 'en' => 'Startups', 'ru' => 'Стартапы', 'kk' => 'Стартаптар'],
        ];

        foreach ($interests as $index => $interest) {
            $exists = DB::table('interests')->where('slug', $interest['slug'])->exists();

            if ($exists) {
                continue;
            }

            $interestId = DB::table('interests')->insertGetId([
                'slug' => $interest['slug'],
                'icon' => $interest['icon'],
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('interest_translations')->insert([
                ['interest_id' => $interestId, 'language_code' => 'en', 'name' => $interest['en']],
                ['interest_id' => $interestId, 'language_code' => 'ru', 'name' => $interest['ru']],
                ['interest_id' => $interestId, 'language_code' => 'kk', 'name' => $interest['kk']],
            ]);
        }
    }
}
