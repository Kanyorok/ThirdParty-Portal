<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_Pricing', function (Blueprint $table) {
            $table->id('Id');
            $table->string('PriceID');
            $table->foreignId('ItemID')->constrained('t_Items', 'Id');
            //$table->foreignId('SKUCode')->constrained('t_StockItems', 'Id');
            //$table->foreignId('ItemCode')->constrained('t_Items', 'Id');
            $table->foreignId('UOM')->constrained('t_Items', 'Id');
            $table->decimal('EstimatedPrice');
            $table->decimal('ActualPrice');
            $table->string('CurrencyCode')->default('KES');
            $table->date('EffectiveFrom');
            $table->date('EffectiveTo')->nullable();
            $table->boolean('IsDefault')->default(false); 
            $table->string('Source')->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
            

        
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_Pricing');
    }
};