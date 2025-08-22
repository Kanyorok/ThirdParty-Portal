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
        Schema::create('t_SupplierCategories', function (Blueprint $table) {
            $table->id('SupplierCategoryID');
            $table->string('CategoryName', 200);
            $table->string('Description', 500)->nullable();
            $table->boolean('IsActive')->default(true);
            $table->string('CreatedBy', 100);
            $table->timestamp('CreatedOn')->useCurrent();
            $table->string('ModifiedBy', 100)->nullable();
            $table->timestamp('ModifiedOn')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_SupplierCategories');
    }
};
