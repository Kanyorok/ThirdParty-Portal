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
        Schema::table('t_FinanceReceiptAllocations', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['InvoiceID'])->references(['Id'])->on('t_FinanceInvoices')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ReceiptID'])->references(['Id'])->on('t_FinanceReceipts')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FinanceReceiptAllocations', function (Blueprint $table) {
            $table->dropForeign('t_financereceiptallocations_createdby_foreign');
            $table->dropForeign('t_financereceiptallocations_deletedby_foreign');
            $table->dropForeign('t_financereceiptallocations_invoiceid_foreign');
            $table->dropForeign('t_financereceiptallocations_modifiedby_foreign');
            $table->dropForeign('t_financereceiptallocations_receiptid_foreign');
        });
    }
};
