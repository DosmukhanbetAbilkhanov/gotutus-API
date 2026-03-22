<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('public_offer_accepted_at')->nullable()->after('last_seen_at');
            $table->string('public_offer_version')->nullable()->after('public_offer_accepted_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['public_offer_accepted_at', 'public_offer_version']);
        });
    }
};
