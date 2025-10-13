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
            // Allow Debit Notes to reference AR invoices; remove strict FK to AP entries
            // Drops: t_financecdnotes_invoicerefno_foreign
            if (Schema::hasColumn('t_FinanceCDNotes', 'InvoiceRefNo')) {
                $table->dropForeign(['InvoiceRefNo']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FinanceCDNotes', function (Blueprint $table) {
            // Restore original FK to AP invoice entries table
            if (Schema::hasColumn('t_FinanceCDNotes', 'InvoiceRefNo')) {
                $table->foreign('InvoiceRefNo')->references('Id')->on('t_FinanceInvoiceEntry');
            }
        });
    }
};


