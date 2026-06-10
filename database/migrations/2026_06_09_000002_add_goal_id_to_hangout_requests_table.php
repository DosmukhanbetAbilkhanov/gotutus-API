<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hangout_requests', function (Blueprint $table) {
            $table->foreignId('goal_id')->nullable()->after('activity_type_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('hangout_requests', function (Blueprint $table) {
            $table->dropForeign(['goal_id']);
            $table->dropColumn('goal_id');
        });
    }
};
