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
        Schema::create('t_Suppliers', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('RoundID')->nullable();
            $table->bigInteger('ThirdPartyID')->nullable();
            $table->boolean('Active_Status')->default(true);
            $table->bigInteger('SupplierCategoryID')->nullable();
            $table->bigInteger('CategoryId')->nullable();

            $table->primary(['Id'], 'pk__t_suppli__3214ec07621cb879');
            $table->unique(['RoundID', 'ThirdPartyID', 'CategoryId'], 'uq_t_suppliers_round_tp_cat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Suppliers');
    }
};
