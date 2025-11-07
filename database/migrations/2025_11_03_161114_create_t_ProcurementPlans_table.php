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
        Schema::create('t_ProcurementPlans', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('ProcurementPeriodId');
            $table->bigInteger('ItemId');
            $table->string('Category', 100)->nullable();
            $table->string('UOM', 50)->nullable();
            $table->decimal('Quantity', 12)->nullable();
            $table->string('PlannedQuarter', 10)->nullable();
            $table->date('ExpectedDeliveryDate')->nullable();
            $table->decimal('TotalCost', 12)->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_procur__3214ec07c0c3deb0');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ProcurementPlans');
    }
};
