<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * @deprecated Use LegalDocumentSeeder instead — it seeds both Public Offer and Privacy Policy.
 */
class LegalPageSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(LegalDocumentSeeder::class);
    }
}
