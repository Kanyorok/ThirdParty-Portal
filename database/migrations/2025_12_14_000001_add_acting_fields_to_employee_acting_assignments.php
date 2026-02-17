<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_HREmployeeActingAssignments', function (Blueprint $table) {
            if (!Schema::hasColumn('t_HREmployeeActingAssignments', 'ActingReferenceSalary')) {
                $table->decimal('ActingReferenceSalary', 18, 2)->nullable()->after('ActingRoleID');
            }
            if (!Schema::hasColumn('t_HREmployeeActingAssignments', 'ActingAllowanceRate')) {
                $table->decimal('ActingAllowanceRate', 9, 4)->nullable()->after('ActingReferenceSalary'); // e.g. 0.20 = 20%
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_HREmployeeActingAssignments', function (Blueprint $table) {
            if (Schema::hasColumn('t_HREmployeeActingAssignments', 'ActingReferenceSalary')) {
                $table->dropColumn('ActingReferenceSalary');
            }
            if (Schema::hasColumn('t_HREmployeeActingAssignments', 'ActingAllowanceRate')) {
                $table->dropColumn('ActingAllowanceRate');
            }
        });
    }
};
