<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_HRPayrollAllowances', function (Blueprint $table) {
            if (!Schema::hasColumn('t_HRPayrollAllowances', 'DebitGLAccountID')) {
                $table->unsignedBigInteger('DebitGLAccountID')->nullable()->after('Description');
            }
            if (!Schema::hasColumn('t_HRPayrollAllowances', 'CreditGLAccountID')) {
                $table->unsignedBigInteger('CreditGLAccountID')->nullable()->after('DebitGLAccountID');
            }
        });

        Schema::table('t_HRPayrollDeductions', function (Blueprint $table) {
            if (!Schema::hasColumn('t_HRPayrollDeductions', 'DebitGLAccountID')) {
                $table->unsignedBigInteger('DebitGLAccountID')->nullable()->after('Description');
            }
            if (!Schema::hasColumn('t_HRPayrollDeductions', 'CreditGLAccountID')) {
                $table->unsignedBigInteger('CreditGLAccountID')->nullable()->after('DebitGLAccountID');
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_HRPayrollAllowances', function (Blueprint $table) {
            if (Schema::hasColumn('t_HRPayrollAllowances', 'CreditGLAccountID')) {
                $table->dropColumn('CreditGLAccountID');
            }
            if (Schema::hasColumn('t_HRPayrollAllowances', 'DebitGLAccountID')) {
                $table->dropColumn('DebitGLAccountID');
            }
        });

        Schema::table('t_HRPayrollDeductions', function (Blueprint $table) {
            if (Schema::hasColumn('t_HRPayrollDeductions', 'CreditGLAccountID')) {
                $table->dropColumn('CreditGLAccountID');
            }
            if (Schema::hasColumn('t_HRPayrollDeductions', 'DebitGLAccountID')) {
                $table->dropColumn('DebitGLAccountID');
            }
        });
    }
};

