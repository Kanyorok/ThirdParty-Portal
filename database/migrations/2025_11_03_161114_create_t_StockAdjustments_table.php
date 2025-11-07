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
        Schema::create('t_StockAdjustments', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('AdjustmentId')->nullable();
            $table->date('AdjustmentDate');
            $table->string('Branch');
            $table->string('Status');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('AdjustedBy')->nullable();

            $table->primary(['Id'], 'pk__t_stocka__3214ec07125ef219');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_StockAdjustments');
    }
};
