<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hangout_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hangout_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rater_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('rated_user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['hangout_request_id', 'rater_user_id', 'rated_user_id'], 'hangout_ratings_unique');
            $table->index('rated_user_id');
            $table->index(['hangout_request_id', 'rater_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hangout_ratings');
    }
};
