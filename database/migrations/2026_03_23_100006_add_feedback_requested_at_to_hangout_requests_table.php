<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hangout_requests', function (Blueprint $table) {
            $table->timestamp('feedback_requested_at')->nullable()->after('bill_split');
        });
    }

    public function down(): void
    {
        Schema::table('hangout_requests', function (Blueprint $table) {
            $table->dropColumn('feedback_requested_at');
        });
    }
};
