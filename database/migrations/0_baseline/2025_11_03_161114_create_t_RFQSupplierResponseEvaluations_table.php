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
        Schema::create('t_RFQSupplierResponseEvaluations', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('RFQEvaluationId');
            $table->bigInteger('SupplierId');
            $table->bigInteger('CriteriaId');
            $table->decimal('Score', 5);
            $table->text('Comments')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_rfqsup__3214ec078e3083b3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_RFQSupplierResponseEvaluations');
    }
};
