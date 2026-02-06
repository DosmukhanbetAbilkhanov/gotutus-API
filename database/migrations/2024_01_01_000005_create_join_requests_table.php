<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('join_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hangout_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('place_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('pending');
            $table->timestamp('confirmed_at')->nullable();
            $table->text('message')->nullable();
            $table->timestamps();

            $table->unique(['hangout_request_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('join_requests');
    }
};
