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
        Schema::create('t_RequisitionLines', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('Type');
            $table->bigInteger('Item');
            $table->text('Description');
            $table->string('UOM');
            $table->float('Quantity')->default(0);
            $table->decimal('ExpectedPrice', 20, 3)->default(0);
            $table->bigInteger('UrgencyID');
            $table->bigInteger('StatusID');
            $table->bigInteger('PlanLineRef')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('RequisitionID')->nullable();

            $table->primary(['Id'], 'pk__t_requis__3214ec070c5b7ed4');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_RequisitionLines');
    }
};
