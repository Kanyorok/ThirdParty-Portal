<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_Orders', function (Blueprint $table) {
            if (!Schema::hasColumn('t_Orders', 'SourceType')) {
                $table->string('SourceType', 16)->nullable();
            }
            if (!Schema::hasColumn('t_Orders', 'SourceId')) {
                $table->bigInteger('SourceId')->nullable();
            }
        });

        // Create filtered unique index to avoid duplicate conversion (only when SourceId is not null)
        try {
            DB::statement("CREATE UNIQUE INDEX UX_t_Orders_Source ON t_Orders (SourceType, SourceId) WHERE SourceId IS NOT NULL");
        } catch (\Throwable $e) {
            // Ignore if index already exists or database doesn't support filtered indexes in current version
        }
    }

    public function down(): void
    {
        try {
            DB::statement("DROP INDEX UX_t_Orders_Source ON t_Orders");
        } catch (\Throwable $e) {
            // ignore
        }

        Schema::table('t_Orders', function (Blueprint $table) {
            if (Schema::hasColumn('t_Orders', 'SourceType')) {
                $table->dropColumn('SourceType');
            }
            if (Schema::hasColumn('t_Orders', 'SourceId')) {
                $table->dropColumn('SourceId');
            }
        });
    }
};



