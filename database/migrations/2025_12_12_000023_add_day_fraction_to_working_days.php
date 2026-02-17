<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_HRWorkingDays', function (Blueprint $table) {
            if (!Schema::hasColumn('t_HRWorkingDays', 'DayFraction')) {
                $table->decimal('DayFraction', 3, 2)->default(1.00)->after('IsWorking');
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_HRWorkingDays', function (Blueprint $table) {
            if (Schema::hasColumn('t_HRWorkingDays', 'DayFraction')) {
                $table->dropColumn('DayFraction');
            }
        });
    }
};
