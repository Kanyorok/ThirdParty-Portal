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
        Schema::create('t_ProcurementPlans', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('ProcurementPeriodId')->constrained('t_ProcurementPeriods')->onDelete('cascade');
            $table->foreignId('ItemId')->constrained('t_Items');
            $table->integer('Quantity');
            $table->decimal('TotalCost', 12, 2)->nullable();
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
        Schema::dropIfExists('t_ProcurementPlans');
    }
};
