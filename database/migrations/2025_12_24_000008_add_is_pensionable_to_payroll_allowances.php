<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_HRPayrollAllowances', function (Blueprint $table) {
            if (!Schema::hasColumn('t_HRPayrollAllowances', 'IsPensionable')) {
                $table->boolean('IsPensionable')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_HRPayrollAllowances', function (Blueprint $table) {
            if (Schema::hasColumn('t_HRPayrollAllowances', 'IsPensionable')) {
                $table->dropColumn('IsPensionable');
            }
        });
    }
};
