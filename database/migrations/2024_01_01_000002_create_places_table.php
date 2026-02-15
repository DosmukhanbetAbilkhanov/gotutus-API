<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('places', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('place_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('place_id')->constrained()->cascadeOnDelete();
            $table->string('language_code', 5);
            $table->string('name');
            $table->string('address')->nullable();
            $table->unique(['place_id', 'language_code']);
        });

        Schema::create('activity_type_place', function (Blueprint $table) {
            $table->foreignId('activity_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('place_id')->constrained()->cascadeOnDelete();
            $table->unique(['activity_type_id', 'place_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_type_place');
        Schema::dropIfExists('place_translations');
        Schema::dropIfExists('places');
    }
};
