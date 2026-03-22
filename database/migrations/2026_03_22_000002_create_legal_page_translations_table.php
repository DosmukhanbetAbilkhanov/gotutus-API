<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_page_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legal_page_id')->constrained('legal_pages')->cascadeOnDelete();
            $table->string('language_code', 2);
            $table->string('title');
            $table->longText('content');

            $table->unique(['legal_page_id', 'language_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_page_translations');
    }
};
