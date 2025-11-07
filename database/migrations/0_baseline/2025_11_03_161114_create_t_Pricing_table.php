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
        Schema::create('t_Pricing', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('PriceID')->nullable();
            $table->bigInteger('ItemID');
            $table->bigInteger('UOM');
            $table->decimal('ActualPrice')->nullable();
            $table->string('CurrencyCode')->default('KES');
            $table->date('EffectiveFrom')->nullable();
            $table->date('EffectiveTo')->nullable();
            $table->boolean('IsDefault')->default(false);
            $table->string('Source')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->string('ItemCode', 100)->nullable();

            $table->primary(['Id'], 'pk__t_pricin__3214ec07ce844e3f');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Pricing');
    }
};
