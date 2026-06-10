<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ActivityType;
use App\Models\Goal;
use Illuminate\Database\Seeder;

class GoalSeeder extends Seeder
{
    public function run(): void
    {
        $goals = [
            ['slug' => 'just_chat', 'icon' => "\u{1F4AC}", 'sort_order' => 1, 'en' => 'Just to chat', 'ru' => 'Просто пообщаться', 'kk' => 'Жай ғана сөйлесу'],
            ['slug' => 'networking', 'icon' => "\u{1F91D}", 'sort_order' => 2, 'en' => 'Networking', 'ru' => 'Нетворкинг', 'kk' => 'Нетворкинг (таныстық)'],
            ['slug' => 'new_people', 'icon' => "\u{1F44B}", 'sort_order' => 3, 'en' => 'Meet new people', 'ru' => 'Познакомиться с новыми людьми', 'kk' => 'Жаңа адамдармен танысу'],
            ['slug' => 'romantic', 'icon' => "\u{2764}\u{FE0F}", 'sort_order' => 4, 'en' => 'Romantic date', 'ru' => 'Романтическое свидание', 'kk' => 'Романтикалық кездесу'],
            ['slug' => 'chemistry', 'icon' => "\u{2728}", 'sort_order' => 5, 'en' => 'Looking for chemistry', 'ru' => 'В поисках «искры»', 'kk' => 'Сезім іздеу («ұшқын»)'],
            ['slug' => 'get_to_know', 'icon' => "\u{1FAC2}", 'sort_order' => 6, 'en' => 'Get to know each other', 'ru' => 'Узнать друг друга лучше', 'kk' => 'Бір-бірін жақсырақ тану'],
            ['slug' => 'serious_rel', 'icon' => "\u{1F48D}", 'sort_order' => 7, 'en' => 'Serious relationship', 'ru' => 'Серьезные отношения', 'kk' => 'Маңызды қарым-қатынас'],
            ['slug' => 'play_win', 'icon' => "\u{1F3C6}", 'sort_order' => 8, 'en' => 'Play to win', 'ru' => 'Играть на победу', 'kk' => 'Жеңіс үшін ойнау'],
            ['slug' => 'teammate', 'icon' => "\u{1F91C}", 'sort_order' => 9, 'en' => 'Find a teammate', 'ru' => 'Найти партнера по команде', 'kk' => 'Командалас табу'],
            ['slug' => 'friendly_match', 'icon' => "\u{1F3C5}", 'sort_order' => 10, 'en' => 'Friendly match', 'ru' => 'Товарищеский матч', 'kk' => 'Жолдастық кездесу'],
            ['slug' => 'share_vibes', 'icon' => "\u{1F3B6}", 'sort_order' => 11, 'en' => 'Share the vibes', 'ru' => 'Разделить атмосферу', 'kk' => 'Көңіл-күймен бөлісу'],
            ['slug' => 'watch_discuss', 'icon' => "\u{1F3AC}", 'sort_order' => 12, 'en' => 'Watch and discuss', 'ru' => 'Посмотреть и обсудить', 'kk' => 'Көру және талқылау'],
            ['slug' => 'have_fun', 'icon' => "\u{1F389}", 'sort_order' => 13, 'en' => 'Have fun together', 'ru' => 'Повеселиться вместе', 'kk' => 'Бірге көңіл көтеру'],
            ['slug' => 'relax', 'icon' => "\u{1F9D8}", 'sort_order' => 14, 'en' => 'Relax and recharge', 'ru' => 'Отдохнуть и перезагрузиться', 'kk' => 'Демалу және күш жинау'],
            ['slug' => 'heart_talk', 'icon' => "\u{1F49C}", 'sort_order' => 15, 'en' => 'Heart-to-heart talk', 'ru' => 'Душевный разговор', 'kk' => 'Шынайы сырласу'],
            ['slug' => 'fresh_air', 'icon' => "\u{1F33F}", 'sort_order' => 16, 'en' => 'Fresh air', 'ru' => 'Свежий воздух', 'kk' => 'Таза ауа'],
        ];

        foreach ($goals as $data) {
            $goal = Goal::firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'icon' => $data['icon'],
                    'sort_order' => $data['sort_order'],
                    'is_active' => true,
                ],
            );

            foreach (['en', 'ru', 'kk'] as $lang) {
                $goal->translations()->firstOrCreate(
                    ['language_code' => $lang],
                    ['name' => $data[$lang]],
                );
            }
        }

        // Pivot mapping: activity_type slug -> goal slugs
        $mapping = [
            // A. Social & Foodie
            'beer'       => ['just_chat', 'networking', 'new_people', 'romantic', 'chemistry', 'get_to_know', 'serious_rel'],
            'coffee'     => ['just_chat', 'networking', 'new_people', 'romantic', 'chemistry', 'get_to_know', 'serious_rel'],
            'sushi'      => ['just_chat', 'networking', 'new_people', 'romantic', 'chemistry', 'get_to_know', 'serious_rel'],
            'fast_food'  => ['just_chat', 'networking', 'new_people', 'romantic', 'chemistry', 'get_to_know', 'serious_rel'],
            'kumys'      => ['just_chat', 'networking', 'new_people', 'romantic', 'chemistry', 'get_to_know', 'serious_rel'],
            'hookah'     => ['just_chat', 'networking', 'new_people', 'romantic', 'chemistry', 'get_to_know', 'serious_rel'],
            'restaurant' => ['just_chat', 'networking', 'new_people', 'romantic', 'chemistry', 'get_to_know', 'serious_rel'],
            // B. Active & Competitive
            'bowling'     => ['play_win', 'teammate', 'friendly_match'],
            'billiards'   => ['play_win', 'teammate', 'friendly_match'],
            'quiz'        => ['play_win', 'teammate', 'friendly_match'],
            'tennis'      => ['play_win', 'teammate', 'friendly_match'],
            'football'    => ['play_win', 'teammate', 'friendly_match'],
            'board-games' => ['play_win', 'teammate', 'friendly_match'],
            'pc_club'     => ['play_win', 'teammate', 'friendly_match'],
            // C. Entertainment & Vibes
            'cinema'  => ['share_vibes', 'watch_discuss', 'have_fun', 'romantic', 'chemistry', 'get_to_know', 'serious_rel'],
            'concert' => ['share_vibes', 'watch_discuss', 'have_fun', 'romantic', 'chemistry', 'get_to_know', 'serious_rel'],
            'standup' => ['share_vibes', 'watch_discuss', 'have_fun', 'romantic', 'chemistry', 'get_to_know', 'serious_rel'],
            'karaoke' => ['share_vibes', 'watch_discuss', 'have_fun', 'romantic', 'chemistry', 'get_to_know', 'serious_rel'],
            // D. Wellness & Relax
            'bathhouse' => ['relax', 'heart_talk', 'fresh_air'],
            'walk'      => ['relax', 'heart_talk', 'fresh_air'],
            // Unmapped types (assign to closest category)
            'kvest'     => ['play_win', 'teammate', 'have_fun'],
            'paintball' => ['play_win', 'teammate', 'friendly_match'],
        ];

        $goalIds = Goal::pluck('id', 'slug');

        foreach ($mapping as $activitySlug => $goalSlugs) {
            $activityType = ActivityType::where('slug', $activitySlug)->first();
            if (! $activityType) {
                continue;
            }

            $pivotGoalIds = collect($goalSlugs)
                ->map(fn (string $slug) => $goalIds[$slug] ?? null)
                ->filter()
                ->all();

            $activityType->goals()->syncWithoutDetaching($pivotGoalIds);
        }
    }
}
