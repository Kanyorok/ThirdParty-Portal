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
        Schema::table('t_TransactionReceiptItems', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['Item'])->references(['Id'])->on('t_Items')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ReceiptId'])->references(['Id'])->on('t_TransactionReceipts')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['Store'])->references(['Id'])->on('t_Stores')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_TransactionReceiptItems', function (Blueprint $table) {
            $table->dropForeign('t_transactionreceiptitems_createdby_foreign');
            $table->dropForeign('t_transactionreceiptitems_deletedby_foreign');
            $table->dropForeign('t_transactionreceiptitems_item_foreign');
            $table->dropForeign('t_transactionreceiptitems_modifiedby_foreign');
            $table->dropForeign('t_transactionreceiptitems_receiptid_foreign');
            $table->dropForeign('t_transactionreceiptitems_store_foreign');
        });
    }
};
