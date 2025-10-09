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
        Schema::create('t_Items', static function (Blueprint $table) {
            $table->id('Id');
            $table->string('ItemCode')->unique();
            $table->string('BarCode');
            $table->string('ItemName');
            $table->string('ItemType');//to system  codes
            $table->foreignId('Category')->constrained('t_ItemCategories', 'Id');
            $table->string('UOM');//syetem codes
            $table->string('InventoryType'); // system codes
            $table->unsignedBigInteger('ImageId')->nullable();
            $table->string('ItemDescription')->nullable();
            $table->string('DocumentUpload')->nullable();

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
        Schema::dropIfExists('t_Items');
    }
};
