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
        Schema::create('t_InventoryHold', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('InventoryHoldID')->nullable()->unique();
            $table->bigInteger('ItemID');
            $table->bigInteger('BranchID');
            $table->bigInteger('Store')->nullable();
            $table->decimal('Quantity', 18)->default(0);
            $table->string('Reason')->nullable();
            $table->bigInteger('Source')->nullable();
            $table->integer('SourceID')->nullable();
            $table->string('Status')->nullable();
            $table->text('Remarks')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_invent__3214ec07e6343e54');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_InventoryHold');
    }
};
