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
            $table->bigIncrements('SupplierCategoryID');
            $table->string('CategoryName', 200);
            $table->string('Description', 500)->nullable();
            $table->boolean('IsActive')->default(true);
            $table->string('CreatedBy', 100);
            $table->dateTime('CreatedOn')->useCurrent();
            $table->string('ModifiedBy', 100)->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->string('DeletedBy', 100)->nullable();

            $table->primary(['SupplierCategoryID'], 'pk__t_suppli__50e4495036a3beb5');
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
