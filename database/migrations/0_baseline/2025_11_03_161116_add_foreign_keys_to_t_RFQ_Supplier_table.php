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
        Schema::table('t_RFQ_Supplier', function (Blueprint $table) {
            $table->foreign(['RFQId'])->references(['Id'])->on('t_RFQ')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['SupplierId'])->references(['Id'])->on('t_Suppliers')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_RFQ_Supplier', function (Blueprint $table) {
            $table->dropForeign('t_rfq_supplier_rfqid_foreign');
            $table->dropForeign('t_rfq_supplier_supplierid_foreign');
        });
    }
};
