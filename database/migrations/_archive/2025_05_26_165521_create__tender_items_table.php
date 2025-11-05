<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_TenderItems', function (Blueprint $table) {
            $table->id();

            $table->integer('TenderID')->unsigned();

            $table->string('SourceType', 20); // 'PLAN' or 'MANUAL'

            $table->integer('ItemID')->nullable();
            $table->integer('PlanItemID')->nullable(); // for PLAN sources

            $table->string('ManualItemDescription', 255)->nullable(); // for MANUAL sources

            $table->integer('PlannedQty')->nullable(); // from plan
            $table->integer('QtyToTender');

            $table->string('ItemCategory', 50); // 'Technology', 'Stationery', etc.

            $table->string('Remarks', 255)->nullable();

            $table->string('RelatedPRID', 20)->nullable(); // Optional PR reference

            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
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
