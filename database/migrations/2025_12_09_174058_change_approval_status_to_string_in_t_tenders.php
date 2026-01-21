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
        Schema::table('t_tenders', function (Blueprint $table) {
            //
     // 1. Drop the default constraint using raw SQL (SQL Server specific)
        $constraintName = DB::selectOne(
            "SELECT Name FROM sys.default_constraints 
             WHERE parent_object_id = OBJECT_ID('t_Tenders') 
             AND parent_column_id = (SELECT column_id FROM sys.columns WHERE object_id = OBJECT_ID('t_Tenders') AND name = 'ApprovalStatus')"
        );

        if ($constraintName) {
            DB::statement("ALTER TABLE t_Tenders DROP CONSTRAINT " . $constraintName->Name);
        }

        // 2. Change the column type to string using Laravel Schema
        Schema::table('t_Tenders', function (Blueprint $table) {
            // Change to string, nullable, and set default to '0' (as a string)
            $table->string('ApprovalStatus', 50)->nullable()->default('0')->change();
        });
        });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to tinyint if needed
        Schema::table('t_Tenders', function (Blueprint $table) {
            // Note: This might fail if the column contains non-numeric strings (like 'dr')
            // So we usually leave data migration logic out of down() for type changes
            $table->tinyInteger('ApprovalStatus')->default(0)->change();
        });
    }
};
