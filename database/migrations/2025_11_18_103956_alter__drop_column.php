<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
         Schema::table('t_WorkFlowLimits', function (Blueprint $table) {

            // 1. Drop index for Source (SQL Server requirement)
            if (Schema::hasColumn('t_WorkFlowLimits', 'Source')) {
                DB::statement('DROP INDEX t_workflowlimits_source_index ON t_WorkFlowLimits');
            }

            // 2. Drop default constraint if any
            DB::statement("
                DECLARE @sql NVARCHAR(MAX) = '';
                SELECT @sql += 'ALTER TABLE t_WorkFlowLimits DROP CONSTRAINT ' + OBJECT_NAME(default_object_id) + ';'
                FROM sys.columns
                WHERE object_id = OBJECT_ID('t_WorkFlowLimits')
                  AND name = 'Source'
                  AND default_object_id <> 0;
                EXEC(@sql);
            ");

            // 3. Drop Source column
            if (Schema::hasColumn('t_WorkFlowLimits', 'Source')) {
                $table->dropColumn('Source');
            }

        });

        // 4. Add the WorkFlowStageId column separately (SQL Server requires separate migration steps)
        Schema::table('t_WorkFlowLimits', function (Blueprint $table) {
            if (!Schema::hasColumn('t_WorkFlowLimits', 'WorkFlowStageId')) {
                $table->foreignId('WorkFlowStageId')->nullable()
                      ->constrained('t_WorkFlowStages', 'Id');
            }
        });
      
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_WorkFlowLimits', function (Blueprint $table) {

            // Restore Source column
            if (!Schema::hasColumn('t_WorkFlowLimits', 'Source')) {
                $table->string('Source')->nullable();
            }

            // OPTIONAL: drop WorkFlowStageId if you want to revert
            // (only if it didn't exist before)
            if (Schema::hasColumn('t_WorkFlowLimits', 'WorkFlowStageId')) {
                $table->dropForeign(['WorkFlowStageId']);
                $table->dropColumn('WorkFlowStageId');
            }

        });
    }
    
};
