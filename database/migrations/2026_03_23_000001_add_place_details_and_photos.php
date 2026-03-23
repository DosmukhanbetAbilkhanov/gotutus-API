<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('places', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('city_id');
            $table->string('phone')->nullable()->after('logo_path');
            $table->string('website')->nullable()->after('phone');
            $table->string('instagram')->nullable()->after('website');
            $table->string('two_gis_url')->nullable()->after('instagram');
        });

        Schema::table('place_translations', function (Blueprint $table) {
            $table->text('description')->nullable()->after('address');
        });

        Schema::create('place_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('place_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('place_photos');

        Schema::table('place_translations', function (Blueprint $table) {
            $table->dropColumn('description');
        });

        Schema::table('places', function (Blueprint $table) {
            $table->dropColumn(['logo_path', 'phone', 'website', 'instagram', 'two_gis_url']);
        });
    }
};
