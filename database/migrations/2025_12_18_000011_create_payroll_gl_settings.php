<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_HRPayrollGLSettings', function (Blueprint $table) {
            $table->id('Id');

            $table->unsignedBigInteger('PayrollControlGLAccountID')->nullable();
            $table->unsignedBigInteger('BasicSalaryExpenseGLAccountID')->nullable();

            // Optional settlement GLs (used later for payments / CBS integration)
            $table->unsignedBigInteger('SalaryBankGLAccountID')->nullable();
            $table->unsignedBigInteger('SalaryCBSSettlementGLAccountID')->nullable();

            $table->unsignedBigInteger('GratuityExpenseGLAccountID')->nullable();
            $table->unsignedBigInteger('GratuityLiabilityGLAccountID')->nullable();

            $table->string('CurrencyID', 10)->nullable();

            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HRPayrollGLSettings');
    }
};
