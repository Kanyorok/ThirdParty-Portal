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
        Schema::table('t_FinanceInvoiceEntry', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CurrencyID'])->references(['Id'])->on('t_Currencies')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['GRNReference'])->references(['id'])->on('t_GoodsReceipts')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['POReference'])->references(['Id'])->on('t_Orders')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FinanceInvoiceEntry', function (Blueprint $table) {
            $table->dropForeign('t_financeinvoiceentry_createdby_foreign');
            $table->dropForeign('t_financeinvoiceentry_currencyid_foreign');
            $table->dropForeign('t_financeinvoiceentry_deletedby_foreign');
            $table->dropForeign('t_financeinvoiceentry_grnreference_foreign');
            $table->dropForeign('t_financeinvoiceentry_modifiedby_foreign');
            $table->dropForeign('t_financeinvoiceentry_poreference_foreign');
        });
    }
};
