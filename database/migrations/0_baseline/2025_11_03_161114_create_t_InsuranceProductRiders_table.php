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
        Schema::create('t_InsuranceProductRiders', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('InsuranceProviderId');
            $table->bigInteger('Product');
            $table->string('RiderName');
            $table->string('Description')->nullable();
            $table->float('AdditionalPremium');
            $table->boolean('IsOptional');
            $table->boolean('IsActive');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_insura__3214ec07498a6ff8');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_InsuranceProductRiders');
    }
};
