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
        Schema::create('t_DocumentCheckOuts', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('DocumentId');
            $table->text('CheckOutRemark')->nullable();
            $table->text('CheckInRemark')->nullable();
            $table->char('Status', 3);
            $table->dateTime('Dated');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_docume__3214ec072bcb7bcb');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_DocumentCheckOuts');
    }
};
