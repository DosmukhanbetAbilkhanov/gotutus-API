<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hangout_requests', function (Blueprint $table) {
            $table->foreignId('place_advertisement_id')
                ->nullable()
                ->after('place_id')
                ->constrained('place_advertisements')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('hangout_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('place_advertisement_id');
        });
    }
};
