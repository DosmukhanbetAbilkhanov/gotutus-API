<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_photos', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('photo_url');
            $table->string('rejection_reason')->nullable()->after('status');
        });

        // Migrate existing data: approved photos get 'approved' status, rest stay 'pending'
        DB::table('user_photos')
            ->where('is_approved', true)
            ->update(['status' => 'approved']);

        DB::table('user_photos')
            ->where('is_approved', false)
            ->update(['status' => 'pending']);
    }

    public function down(): void
    {
        Schema::table('user_photos', function (Blueprint $table) {
            $table->dropColumn(['status', 'rejection_reason']);
        });
    }
};
