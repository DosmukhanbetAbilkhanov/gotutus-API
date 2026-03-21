<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hangout_requests', function (Blueprint $table) {
            $table->string('bill_split')->nullable()->after('max_participants');
        });
    }

    public function down(): void
    {
        Schema::table('hangout_requests', function (Blueprint $table) {
            $table->dropColumn('bill_split');
        });
    }
};
