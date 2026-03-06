<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('join_requests', function (Blueprint $table) {
            $table->renameColumn('place_id', 'suggested_place_id');
        });
    }

    public function down(): void
    {
        Schema::table('join_requests', function (Blueprint $table) {
            $table->renameColumn('suggested_place_id', 'place_id');
        });
    }
};
