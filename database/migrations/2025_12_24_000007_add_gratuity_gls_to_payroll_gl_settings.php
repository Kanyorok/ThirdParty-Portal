<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_HRPayrollGLSettings', function (Blueprint $table) {
            if (!Schema::hasColumn('t_HRPayrollGLSettings', 'GratuityExpenseGLAccountID')) {
                $table->unsignedBigInteger('GratuityExpenseGLAccountID')->nullable();
            }
            if (!Schema::hasColumn('t_HRPayrollGLSettings', 'GratuityLiabilityGLAccountID')) {
                $table->unsignedBigInteger('GratuityLiabilityGLAccountID')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_HRPayrollGLSettings', function (Blueprint $table) {
            if (Schema::hasColumn('t_HRPayrollGLSettings', 'GratuityLiabilityGLAccountID')) {
                $table->dropColumn('GratuityLiabilityGLAccountID');
            }
            if (Schema::hasColumn('t_HRPayrollGLSettings', 'GratuityExpenseGLAccountID')) {
                $table->dropColumn('GratuityExpenseGLAccountID');
            }
        });
    }
};
