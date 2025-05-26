<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTRfqLinesTable extends Migration
{
    public function up()
    {
        Schema::table('t_RFQLines', function (Blueprint $table) {
            // Drop old column
            $table->dropColumn('SupplierId');

            // Add nullable foreign key column
            $table->unsignedBigInteger('RequisitionLineId')->nullable();

            // Foreign key constraint
            $table->foreign('RequisitionLineId')
                ->references('id')
                ->on('t_RequisitionLines')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('t_RFQLines', function (Blueprint $table) {
            // Remove foreign key and column
            $table->dropForeign(['RequisitionLineId']);
            $table->dropColumn('RequisitionLineId');

            // Re-add old column
            $table->unsignedBigInteger('SupplierId')->nullable(); // Adjust type as needed
        });
    }
}
