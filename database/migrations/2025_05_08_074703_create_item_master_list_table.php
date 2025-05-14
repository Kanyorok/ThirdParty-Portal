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
        Schema::create('t_ItemMasterList', function (Blueprint $table) {
            $table->id('Id');
            $table->string('ItemCode')->unique();
            $table->string('BarCode');
            $table->string('ItemName');
            $table->string('ItemType');
            $table->foreignId('Category')->constrained('t_ItemCategories','id');
            $table->foreignId('SubCategory')->constrained('t_ItemSubCategories','Id');
            $table->string('UOM');
            $table->string('InventoryType');
            $table->string('ImageUpload')->nullable();
            $table->string('ItemDescription')->nullable();
            $table->string('DocumentUpload')->nullable();
            $table->timestamp('CreatedOn')->useCurrent();
            $table->timestamp('ModifiedOn')->useCurrent();
            $table->timestamp('DeletedOn')->useCurrent();

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
        Schema::dropIfExists('t_ItemMasterList');
    }
};
