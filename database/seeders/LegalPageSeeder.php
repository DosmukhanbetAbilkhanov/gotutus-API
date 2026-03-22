<?php

namespace Database\Seeders;

use App\Models\LegalPage;
use Illuminate\Database\Seeder;

class LegalPageSeeder extends Seeder
{
    public function run(): void
    {
        $page = LegalPage::create([
            'slug' => LegalPage::SLUG_PUBLIC_OFFER,
            'version' => '1.0',
            'is_active' => true,
            'published_at' => now(),
        ]);

        $page->translations()->createMany([
            [
                'language_code' => 'en',
                'title' => 'Public Offer Agreement',
                'content' => '<p>This is a placeholder for the public offer agreement. The actual content will be added by administrators.</p>',
            ],
            [
                'language_code' => 'ru',
                'title' => 'Публичная оферта',
                'content' => '<p>Это заполнитель для публичной оферты. Фактическое содержание будет добавлено администраторами.</p>',
            ],
            [
                'language_code' => 'kk',
                'title' => 'Жария оферта',
                'content' => '<p>Бұл жария оферта үшін толтырғыш. Нақты мазмұнды әкімшілер қосады.</p>',
            ],
        ]);
    }
}
