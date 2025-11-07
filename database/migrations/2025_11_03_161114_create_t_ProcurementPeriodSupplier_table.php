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
        Schema::create('t_ProcurementPeriodSupplier', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('SupplierId');
            $table->bigInteger('ProcurementPeriodId');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');

            $table->primary(['Id'], 'pk__t_procur__3214ec07074a83ff');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ProcurementPeriodSupplier');
    }
};
