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
        Schema::create('t_RFQLines', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('RFQLineNo')->unique();
            $table->bigInteger('RFQId');
            $table->bigInteger('ItemId');
            $table->string('ItemName');
            $table->integer('Quantity');
            $table->bigInteger('ItemCategoryId');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('RequisitionId');
            $table->string('UOM');
            $table->bigInteger('RequisitionLineId');

            $table->primary(['Id'], 'pk__t_rfqlin__3214ec07100e9aed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_RFQLines');
    }
};
