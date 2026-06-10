<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('goal_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goal_id')->constrained()->cascadeOnDelete();
            $table->string('language_code', 5);
            $table->string('name');
            $table->unique(['goal_id', 'language_code']);
        });

        Schema::create('activity_type_goal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('goal_id')->constrained()->cascadeOnDelete();
            $table->unique(['activity_type_id', 'goal_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_type_goal');
        Schema::dropIfExists('goal_translations');
        Schema::dropIfExists('goals');
    }
};
