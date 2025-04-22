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
        Schema::create('t_SupplierItemCategory', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('SupplierId')->constrained()->onDelete('cascade');
            $table->foreignId('ItemCategoryId')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_item_category');
    }
};
