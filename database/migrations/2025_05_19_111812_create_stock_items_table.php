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
        Schema::create('t_StockItems', static function (Blueprint $table) {
            $table->id('Id');
            $table->string('SKUCode')->unique();
            $table->string('ItemType');
            $table->boolean('Batch');
            $table->boolean('Serial');
            $table->boolean('Perishable');
            $table->boolean('Saleable');
            $table->boolean('Purchasable');
            $table->string('Store');
            $table->string('Branch'); 
            $table->integer('CurrentQty');
            $table->string('Min');
            $table->integer('Reorder');
            $table->integer('Max');
            $table->dateTime('LastReceived');
            $table->boolean('Status');
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
        Schema::dropIfExists('t_StockItems');
    }
};
