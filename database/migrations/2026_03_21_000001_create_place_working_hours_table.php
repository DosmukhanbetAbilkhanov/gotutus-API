<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('place_working_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('place_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 0=Monday .. 6=Sunday
            $table->string('open_time', 5)->nullable(); // HH:MM format, null = closed
            $table->string('close_time', 5)->nullable(); // HH:MM format, null = closed
            $table->timestamps();

            $table->unique(['place_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('place_working_hours');
    }
};
