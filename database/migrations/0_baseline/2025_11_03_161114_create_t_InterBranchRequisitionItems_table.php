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
        Schema::create('t_InterBranchRequisitionItems', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('RequisitionId');
            $table->bigInteger('Item');
            $table->integer('RequestedQty');
            $table->integer('ApprovedQty')->nullable();
            $table->string('Remarks')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->string('ItemCode')->nullable();

            $table->primary(['Id'], 'pk__t_interb__3214ec070928fc63');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_InterBranchRequisitionItems');
    }
};
