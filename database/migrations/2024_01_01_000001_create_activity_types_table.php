<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_types', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('bg_photo')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('activity_type_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_type_id')->constrained()->cascadeOnDelete();
            $table->string('language_code', 5);
            $table->string('name');

            $table->unique(['activity_type_id', 'language_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_type_translations');
        Schema::dropIfExists('activity_types');
    }
};
