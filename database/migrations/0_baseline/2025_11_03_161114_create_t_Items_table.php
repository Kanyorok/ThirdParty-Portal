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
        Schema::create('t_Items', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('ItemCode')->nullable()->unique();
            $table->string('BarCode');
            $table->string('ItemName');
            $table->bigInteger('Category');
            $table->bigInteger('ImageId')->nullable();
            $table->string('ItemDescription')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('ItemType')->nullable();
            $table->bigInteger('UOM')->nullable();
            $table->bigInteger('InventoryType')->nullable();
            $table->bigInteger('ItemPrice')->nullable();
            $table->bigInteger('Status')->nullable();

            $table->primary(['Id'], 'pk__t_items__3214ec07a32c54d2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Items');
    }
};
