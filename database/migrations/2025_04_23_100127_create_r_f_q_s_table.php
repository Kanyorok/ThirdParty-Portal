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
            $table->json('Suppliers');
            $table->timestamp('CreatedAt')->nullable();
            $table->timestamp('UpdatedAt')->nullable();
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
