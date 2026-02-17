<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_HRKPIGoalItems', function (Blueprint $table) {
            $table->decimal('AnnualTarget', 18, 2)->nullable()->after('KpiItemID');
            $table->decimal('PeriodTarget', 18, 2)->nullable()->after('AnnualTarget');
        });
    }

    public function down(): void
    {
        Schema::table('t_HRKPIGoalItems', function (Blueprint $table) {
            $table->dropColumn(['AnnualTarget', 'PeriodTarget']);
        });
    }
};
