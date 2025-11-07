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
        Schema::create('t_SupplierResponseEvaluations', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->tinyInteger('TechnicalQuality');
            $table->text('TechnicalQualityComments')->nullable();
            $table->tinyInteger('Pricing');
            $table->text('PricingComments')->nullable();
            $table->tinyInteger('DeliveryTime');
            $table->text('DeliveryTimeComments')->nullable();
            $table->tinyInteger('PastExperience');
            $table->text('PastExperienceComments')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('SupplierId')->nullable();

            $table->primary(['Id'], 'pk__t_suppli__3214ec072339b6fa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_SupplierResponseEvaluations');
    }
};
