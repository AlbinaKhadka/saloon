<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add nullable service_category_id column first
        Schema::table('services', function (Blueprint $table) {
            $table->unsignedBigInteger('service_category_id')->nullable()->after('id');
        });

        // 2. Ensure default "General" category exists and backfill existing services
        $categoryId = DB::table('service_categories')->where('slug', 'general')->value('id');
        if (! $categoryId) {
            $categoryId = DB::table('service_categories')->insertGetId([
                'name'       => 'General',
                'slug'       => 'general',
                'status'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('services')
            ->whereNull('service_category_id')
            ->update(['service_category_id' => $categoryId]);

        // 3. Make column NOT NULL, attach foreign key, and set composite unique index
        Schema::table('services', function (Blueprint $table) {
            $table->unsignedBigInteger('service_category_id')->nullable(false)->change();
            $table->foreign('service_category_id')
                ->references('id')
                ->on('service_categories')
                ->cascadeOnDelete();

            $table->dropIndex(['slug']);
            $table->unique(['service_category_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign(['service_category_id']);
            $table->dropUnique(['service_category_id', 'slug']);
            $table->unique('slug');
            $table->dropColumn('service_category_id');
        });
    }
};
