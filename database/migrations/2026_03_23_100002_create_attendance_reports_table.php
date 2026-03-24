<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hangout_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reporter_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reported_user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('showed_up');
            $table->timestamps();

            $table->unique(['hangout_request_id', 'reporter_user_id', 'reported_user_id'], 'attendance_reports_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_reports');
    }
};
