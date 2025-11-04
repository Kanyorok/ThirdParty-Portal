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
        Schema::table('t_FinanceGlTransactionsMapping', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModuleID'])->references(['ModuleID'])->on('t_Modules')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['TransactionTypeID'])->references(['Id'])->on('t_FinanceTransactionTypes')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FinanceGlTransactionsMapping', function (Blueprint $table) {
            $table->dropForeign('t_financegltransactionsmapping_createdby_foreign');
            $table->dropForeign('t_financegltransactionsmapping_deletedby_foreign');
            $table->dropForeign('t_financegltransactionsmapping_modifiedby_foreign');
            $table->dropForeign('t_financegltransactionsmapping_moduleid_foreign');
            $table->dropForeign('t_financegltransactionsmapping_transactiontypeid_foreign');
        });
    }
};
