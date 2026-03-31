<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('place_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('place_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 0=Monday .. 6=Sunday
            $table->string('title');
            $table->unsignedTinyInteger('discount_percent')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['place_id', 'day_of_week', 'title'], 'place_day_title_unique');
            $table->index(['place_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('place_promotions');
    }
};
