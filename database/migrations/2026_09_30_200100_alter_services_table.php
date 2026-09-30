<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add nullable service_category_id column if not exists
        if (! Schema::hasColumn('services', 'service_category_id')) {
            Schema::table('services', function (Blueprint $table) {
                $table->unsignedBigInteger('service_category_id')->nullable()->after('id');
            });
        }

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

        // 3. Make column NOT NULL
        Schema::table('services', function (Blueprint $table) {
            $table->unsignedBigInteger('service_category_id')->nullable(false)->change();
        });

        // 4. Attach foreign key
        try {
            Schema::table('services', function (Blueprint $table) {
                $table->foreign('service_category_id')
                    ->references('id')
                    ->on('service_categories')
                    ->cascadeOnDelete();
            });
        } catch (\Throwable $e) {
            // Foreign key may already exist from prior run
        }

        // 5. Drop old unique constraint on slug
        try {
            Schema::table('services', function (Blueprint $table) {
                $table->dropUnique('services_slug_unique');
            });
        } catch (\Throwable $e) {
            // Index might already be dropped
        }

        // 6. Set composite unique index on (service_category_id, slug)
        try {
            Schema::table('services', function (Blueprint $table) {
                $table->unique(['service_category_id', 'slug']);
            });
        } catch (\Throwable $e) {
            // Composite index might already exist
        }
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
