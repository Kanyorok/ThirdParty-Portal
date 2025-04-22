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
        Schema::create('t_RFQ', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('TenderId')->constrained('t_Tenders');
            $table->foreignId('ItemCategoryId')->constrained('t_ItemCategories');
            $table->foreignId('SupplierId')->constrained('t_Suppliers');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id')->comment('User who created the tender');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id')->comment('User who last modified the tender');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_RFQ');
    }
};
