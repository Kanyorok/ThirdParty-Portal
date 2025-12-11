<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_HRPayrollDeductions', function (Blueprint $table) {
            if (!Schema::hasColumn('t_HRPayrollDeductions', 'IsMandatory')) {
                $table->boolean('IsMandatory')->default(false)->after('Description');
            }
            if (!Schema::hasColumn('t_HRPayrollDeductions', 'ShowInPayslip')) {
                $table->boolean('ShowInPayslip')->default(true)->after('IsMandatory');
            }
            if (!Schema::hasColumn('t_HRPayrollDeductions', 'ApplyFor')) {
                $table->string('ApplyFor', 50)->nullable()->after('ShowInPayslip'); // e.g. Regular/Contract/All
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_HRPayrollDeductions', function (Blueprint $table) {
            $table->dropColumn(['IsMandatory', 'ShowInPayslip', 'ApplyFor']);
        });
    }
};
