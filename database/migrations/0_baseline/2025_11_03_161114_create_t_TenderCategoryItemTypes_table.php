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
        Schema::create('t_TenderCategoryItemTypes', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('TenderCategoryId')->index();
            $table->bigInteger('ItemTypeId')->index();
            $table->boolean('IsActive')->default(true);

            $table->primary(['Id'], 'pk__t_tender__3214ec0778c2e872');
            $table->unique(['TenderCategoryId', 'ItemTypeId'], 'uq_tendercategory_itemtype');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_TenderCategoryItemTypes');
    }
};
