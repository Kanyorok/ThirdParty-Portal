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
        Schema::create('t_PlanLineItem', function (Blueprint $table) {
            $table->id('LineItemID'); // Custom primary key
            $table->foreignId('PlanID')->constrained('t_ConsolidatedProcurementPlan', 'PlanID'); // Foreign key to ConsolidatedProcurementPlan
            $table->string('BranchID', 10);
            $table->string('DepartmentID', 10);
            $table->foreignId('ItemID')->constrained('t_Items', 'Id');
            $table->foreignId('CategoryID')->constrained('t_ItemCategories', 'Id');
            $table->integer('MergedQty');
            $table->string('UnitOfMeasure', 50);
            $table->decimal('EstimatedUnitCost', 15, 2);
            $table->decimal('AdjustedCost', 15, 2);
            $table->string('ProcurementMethod');
            $table->date('ExpectedDeliveryDate');
            $table->string('SchedulePeriod');
            $table->string('ExecutionStatus');
            $table->string('BudgetLineID')->nullable();
            $table->text('ChangeRemarks')->nullable();
            $table->boolean('IsDeleted')->default(false);
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->dateTime('ModifiedOn');
            $table->softDeletes('DeletedOn');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_PlanLineItem');
    }
};
