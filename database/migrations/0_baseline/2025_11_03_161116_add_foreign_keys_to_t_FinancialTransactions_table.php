<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('t_FinancialTransactions', function (Blueprint $table) {
            $table->foreign(['BranchID'])->references(['Id'])->on('t_Branches')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DepartmentID'])->references(['Id'])->on('t_Departments')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['GLAccountID'])->references(['Id'])->on('t_FinanceGLAccounts')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ReversedTransactionID'])->references(['Id'])->on('t_FinancialTransactions')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FinancialTransactions', function (Blueprint $table) {
            $table->dropForeign('t_financialtransactions_branchid_foreign');
            $table->dropForeign('t_financialtransactions_createdby_foreign');
            $table->dropForeign('t_financialtransactions_deletedby_foreign');
            $table->dropForeign('t_financialtransactions_departmentid_foreign');
            $table->dropForeign('t_financialtransactions_glaccountid_foreign');
            $table->dropForeign('t_financialtransactions_modifiedby_foreign');
            $table->dropForeign('t_financialtransactions_reversedtransactionid_foreign');
        });
    }
};
