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
        Schema::table('t_FinanceCDNotes', function (Blueprint $table) {
            // drop the foreign key first
            $table->dropForeign(['InvoiceRefNo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FinanceCDNotes', function (Blueprint $table) {
            $table->foreign('InvoiceRefNo')
                ->references('Id')
                ->on('t_FinanceInvoiceEntry');
        });
    }
};
