<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('place_complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hangout_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('place_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->text('description');
            $table->string('status')->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['hangout_request_id', 'user_id', 'type'], 'place_complaints_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('place_complaints');
    }
};
