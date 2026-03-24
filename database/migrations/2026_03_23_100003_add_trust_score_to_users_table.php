<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('trust_score', 3, 2)->nullable()->default(null)->after('public_offer_version');
            $table->unsignedInteger('ratings_count')->default(0)->after('trust_score');
            $table->decimal('average_rating', 3, 2)->nullable()->default(null)->after('ratings_count');
            $table->decimal('attendance_rate', 5, 2)->nullable()->default(null)->after('average_rating');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['trust_score', 'ratings_count', 'average_rating', 'attendance_rate']);
        });
    }
};
