<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // join_requests: status is filtered constantly (pending/approved/confirmed)
        Schema::table('join_requests', function (Blueprint $table) {
            $table->index('status');
            $table->index(['hangout_request_id', 'status']);
        });

        // user_photos: filtered by status (approved/pending/rejected)
        Schema::table('user_photos', function (Blueprint $table) {
            $table->index(['user_id', 'status']);
        });

        // users: feed filtering by gender + age, status checks
        Schema::table('users', function (Blueprint $table) {
            $table->index('status');
            $table->index(['gender', 'age']);
        });

        // blocked_users: reverse lookup by blocked_user_id
        Schema::table('blocked_users', function (Blueprint $table) {
            $table->index('blocked_user_id');
        });

        // conversations: join_request_id FK was added without index
        Schema::table('conversations', function (Blueprint $table) {
            $table->index('join_request_id');
        });

        // hangout_requests: activity_type_id used in filters
        Schema::table('hangout_requests', function (Blueprint $table) {
            $table->index('activity_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('join_requests', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['hangout_request_id', 'status']);
        });

        Schema::table('user_photos', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['gender', 'age']);
        });

        Schema::table('blocked_users', function (Blueprint $table) {
            $table->dropIndex(['blocked_user_id']);
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropIndex(['join_request_id']);
        });

        Schema::table('hangout_requests', function (Blueprint $table) {
            $table->dropIndex(['activity_type_id']);
        });
    }
};
