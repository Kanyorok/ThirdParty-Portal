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
        Schema::create('t_TransferItems', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('TransferId');
            $table->bigInteger('Item');
            $table->float('ApprovedQty');
            $table->float('DispatchedQty');
            $table->string('Remarks')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('UOM')->nullable();
            $table->decimal('UnitCost', 10)->nullable();

            $table->primary(['Id'], 'pk__t_transf__3214ec0727ed62af');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_TransferItems');
    }
};
