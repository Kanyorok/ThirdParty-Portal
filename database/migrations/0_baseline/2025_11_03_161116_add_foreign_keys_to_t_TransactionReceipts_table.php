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
        Schema::table('t_TransactionReceipts', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ReceivedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['TransferId'])->references(['Id'])->on('t_Transfers')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_TransactionReceipts', function (Blueprint $table) {
            $table->dropForeign('t_transactionreceipts_createdby_foreign');
            $table->dropForeign('t_transactionreceipts_deletedby_foreign');
            $table->dropForeign('t_transactionreceipts_modifiedby_foreign');
            $table->dropForeign('t_transactionreceipts_receivedby_foreign');
            $table->dropForeign('t_transactionreceipts_transferid_foreign');
        });
    }
};
