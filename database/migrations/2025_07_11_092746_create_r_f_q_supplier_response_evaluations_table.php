<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_RFQSupplierResponseEvaluations', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('RFQEvaluationId')->constrained('t_RFQEvaluations', 'Id');
            $table->foreignId('SupplierId')->constrained('t_Suppliers', 'Id');
            $table->foreignId('CriteriaId')->constrained('t_RFQCriteria', 'Id');
            $table->decimal('Score', 5, 2);
            $table->text('Comments')->nullable();
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
        Schema::dropIfExists('t_RFQSupplierResponseEvaluations');
    }
};
