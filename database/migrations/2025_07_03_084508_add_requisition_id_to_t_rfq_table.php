<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('t_RFQ', function (Blueprint $table) {
            $table->unsignedBigInteger('RequisitionId')->after('RFQNumber');
            $table->foreign('RequisitionId')->references('Id')->on('t_Requisitions')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_RFQ', function (Blueprint $table) {
            $table->dropForeign(['RequisitionId']);
            $table->dropColumn('RequisitionId');
        });
    }
};
