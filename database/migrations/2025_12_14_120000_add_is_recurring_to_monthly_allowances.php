<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_HRMonthlyAllowances', function (Blueprint $table) {
            if (!Schema::hasColumn('t_HRMonthlyAllowances', 'IsRecurring')) {
                $table->boolean('IsRecurring')->default(false)->after('IsTaxable');
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_HRMonthlyAllowances', function (Blueprint $table) {
            if (Schema::hasColumn('t_HRMonthlyAllowances', 'IsRecurring')) {
                $table->dropColumn('IsRecurring');
            }
        });
    }
};
