<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_HRKPIGoals', function (Blueprint $table) {
            $table->unsignedSmallInteger('PeriodYear')->nullable()->after('PeriodID');
            $table->unsignedTinyInteger('PeriodSegment')->nullable()->after('PeriodYear');
            $table->dropUnique('ux_kpi_goal_employee_period');
            $table->unique(['EmployeeID', 'PeriodID', 'PeriodYear', 'PeriodSegment'], 'ux_kpi_goal_employee_period_year');
        });
    }

    public function down(): void
    {
        Schema::table('t_HRKPIGoals', function (Blueprint $table) {
            $table->dropUnique('ux_kpi_goal_employee_period_year');
            $table->unique(['EmployeeID', 'PeriodID'], 'ux_kpi_goal_employee_period');
            $table->dropColumn(['PeriodYear', 'PeriodSegment']);
        });
    }
};
