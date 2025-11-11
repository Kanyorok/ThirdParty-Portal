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
        Schema::table('t_RentInvoice', function (Blueprint $table) {
            $table->string('Description')->nullable();
            $table->foreignId('Currency')->nullable()->constrained('t_Currencies','Id');
            $table ->foreignId('Tax')->nullable()->constrained('t_FinanceTaxType','Id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_RentInvoice', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['Currency']);
            $table->dropForeign(['Tax']);

            // Then drop the columns
            $table->dropColumn(['Description', 'Currency', 'Tax']);
        });
    }
};
