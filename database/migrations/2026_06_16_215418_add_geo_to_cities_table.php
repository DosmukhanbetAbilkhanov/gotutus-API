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
        Schema::table('cities', function (Blueprint $table) {
            // Center point + radius used to resolve device coordinates to a city.
            $table->decimal('center_latitude', 10, 7)->nullable()->after('ad_frequency');
            $table->decimal('center_longitude', 10, 7)->nullable()->after('center_latitude');
            $table->unsignedInteger('radius_km')->default(40)->after('center_longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn(['center_latitude', 'center_longitude', 'radius_km']);
        });
    }
};
