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
        Schema::table('t_WorkFlowStages', function (Blueprint $table) {
            //
              // 1. Drop the default constraint if it exists
        $defaultConstraint = DB::selectOne("
            SELECT dc.name AS DefaultConstraintName
            FROM sys.default_constraints dc
            INNER JOIN sys.columns c 
                ON dc.parent_object_id = c.object_id 
                AND dc.parent_column_id = c.column_id
            WHERE c.object_id = OBJECT_ID('dbo.t_WorkFlowStages')
              AND c.name = 'Count'
        ");

        if ($defaultConstraint?->DefaultConstraintName) {
            DB::statement("ALTER TABLE dbo.t_WorkFlowStages DROP CONSTRAINT [{$defaultConstraint->DefaultConstraintName}]");
        }

        // 2. Alter the column to allow NULL
        DB::statement("ALTER TABLE dbo.t_WorkFlowStages ALTER COLUMN [Count] INT NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_WorkFlowStages', function (Blueprint $table) {
            //
              // 1. Alter the column back to NOT NULL
        DB::statement("ALTER TABLE dbo.t_WorkFlowStages ALTER COLUMN [Count] INT NOT NULL");

        // 2. Re-add the default constraint as 0
        DB::statement("ALTER TABLE dbo.t_WorkFlowStages ADD DEFAULT (0) FOR [Count]");
        });
    }
};
