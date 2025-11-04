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
        Schema::create('t_TenderItems', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('TenderID');
            $table->string('SourceType', 20);
            $table->integer('ItemID')->nullable();
            $table->integer('PlanItemID')->nullable();
            $table->string('ManualItemDescription')->nullable();
            $table->integer('PlannedQty')->nullable();
            $table->integer('QtyToTender');
            $table->string('ItemCategory', 50);
            $table->string('Remarks')->nullable();
            $table->string('RelatedPRID', 20)->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['id'], 'pk__t_tender__3213e83fec07c12d');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_TenderItems');
    }
};
