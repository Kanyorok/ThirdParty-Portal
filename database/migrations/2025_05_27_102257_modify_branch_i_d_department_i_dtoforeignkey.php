<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('t_PlanLineItem', function (Blueprint $table) {
            // Drop existing string columns
            $table->dropColumn('BranchID');
            $table->dropColumn('DepartmentID');
            
            // Add new foreign key columns
            $table->foreignId('BranchID')->after('PlanID')->constrained('t_Branches');
            $table->foreignId('DepartmentID')->after('BranchID')->constrained('t_Departments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_PlanLineItem', function (Blueprint $table) {
            // Drop foreign key columns
            $table->dropForeign(['BranchID']);
            $table->dropForeign(['DepartmentID']);
            $table->dropColumn('BranchID');
            $table->dropColumn('DepartmentID');
            
            // Restore original string columns
            $table->string('BranchID', 10)->after('PlanID');
            $table->string('DepartmentID', 10)->after('BranchID');
        });
    }
};