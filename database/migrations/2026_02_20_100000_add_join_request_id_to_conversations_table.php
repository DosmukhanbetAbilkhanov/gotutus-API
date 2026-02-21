<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->foreignId('join_request_id')->nullable()->after('hangout_request_id')
                ->constrained()->nullOnDelete();
        });

        // Backfill existing conversations with their join request
        DB::table('conversations')->get()->each(function ($conv) {
            $jr = DB::table('join_requests')
                ->where('hangout_request_id', $conv->hangout_request_id)
                ->whereIn('status', ['approved', 'confirmed'])
                ->first();

            if ($jr) {
                DB::table('conversations')
                    ->where('id', $conv->id)
                    ->update(['join_request_id' => $jr->id]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('join_request_id');
        });
    }
};
