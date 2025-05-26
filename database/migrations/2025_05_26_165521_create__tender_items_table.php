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
        Schema::create('TenderItems', function (Blueprint $table) {
            $table->id();
            
            $table->integer('TenderID')->unsigned();
            $table->foreign('TenderID')->references('Id')->on('Tenders');

            $table->string('SourceType', 20); // 'PLAN' or 'MANUAL'

            $table->integer('ItemID')->nullable();
            $table->integer('PlanItemID')->nullable(); // for PLAN sources

            $table->string('ManualItemDescription', 255)->nullable(); // for MANUAL sources

            $table->integer('PlannedQty')->nullable(); // from plan
            $table->integer('QtyToTender');

            $table->string('ItemCategory', 50); // 'Technology', 'Stationery', etc.

            $table->string('Remarks', 255)->nullable();

            $table->integer('RelatedPRID')->nullable(); // Optional PR reference

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('TenderItems');
    }
};
