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
        Schema::create('t_ProcurementPeriodSupplier', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('SupplierId')->constrained('t_Suppliers')->onDelete('cascade');
            $table->foreignId('ProcurementPeriodId')->constrained('t_ProcurementPeriods')->onDelete('cascade');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ProcurementPeriodSupplier');
    }
};
