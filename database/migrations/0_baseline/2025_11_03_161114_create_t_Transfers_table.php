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
        Schema::create('t_Transfers', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('TransferID')->nullable();
            $table->bigInteger('RequisitionId');
            $table->date('TransferDate')->nullable();
            $table->string('Status')->nullable();
            $table->bigInteger('FromBranch')->nullable();
            $table->bigInteger('ToBranch');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('TransferredBy')->nullable();
            $table->string('RequisitionType')->nullable();

            $table->primary(['Id'], 'pk__t_transf__3214ec0776c7f1e0');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Transfers');
    }
};
