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
        Schema::create('t_Defects', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('InventoryHoldID');
            $table->bigInteger('ItemID');
            $table->bigInteger('FromBranch')->nullable();
            $table->bigInteger('Store')->nullable();
            $table->decimal('Quantity', 18)->default(0);
            $table->bigInteger('Defect')->nullable();
            $table->bigInteger('Condition')->nullable();
            $table->string('Status')->nullable();
            $table->text('Notes')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_defect__3214ec0758191f0f');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Defects');
    }
};
