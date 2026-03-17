<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_types', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->timestamps();
        });

        $now = now();

        $clientId = DB::table('user_types')->insertGetId([
            'slug' => 'client',
            'name' => 'Client',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $adminId = DB::table('user_types')->insertGetId([
            'slug' => 'admin',
            'name' => 'Admin',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('user_types')->insert([
            'slug' => 'city_manager',
            'name' => 'City Manager',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        Schema::table('users', function (Blueprint $table) use ($clientId) {
            $table->foreignId('user_type_id')
                ->default($clientId)
                ->after('status')
                ->constrained('user_types');
        });

        // Migrate existing is_admin users to admin user type
        DB::table('users')
            ->where('is_admin', true)
            ->update(['user_type_id' => $adminId]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_admin');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('status');
        });

        $adminTypeId = DB::table('user_types')->where('slug', 'admin')->value('id');

        if ($adminTypeId) {
            DB::table('users')
                ->where('user_type_id', $adminTypeId)
                ->update(['is_admin' => true]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_type_id');
        });

        Schema::dropIfExists('user_types');
    }
};
