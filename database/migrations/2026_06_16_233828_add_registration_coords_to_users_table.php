<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Coordinates the account was registered from (audit / abuse review).
            $table->decimal('registration_latitude', 10, 7)->nullable()->after('city_id');
            $table->decimal('registration_longitude', 10, 7)->nullable()->after('registration_latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['registration_latitude', 'registration_longitude']);
        });
    }
};
