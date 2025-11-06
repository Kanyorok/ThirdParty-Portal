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
            $table->id('Id');
            $table->tinyInteger('TechnicalQuality');
            $table->text('TechnicalQualityComments')->nullable();
            $table->tinyInteger('Pricing');
            $table->text('PricingComments')->nullable();
            $table->tinyInteger('DeliveryTime');
            $table->text('DeliveryTimeComments')->nullable();
            $table->tinyInteger('PastExperience');
            $table->text('PastExperienceComments')->nullable();
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
        Schema::dropIfExists('t_SupplierResponseEvaluations');
    }
};
