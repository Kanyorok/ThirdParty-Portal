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
        Schema::create('t_PlanLineItem', function (Blueprint $table) {
            $table->bigIncrements('LineItemID');
            $table->bigInteger('PlanID');
            $table->bigInteger('ItemID');
            $table->bigInteger('CategoryID');
            $table->integer('MergedQty');
            $table->string('UnitOfMeasure', 50);
            $table->decimal('EstimatedUnitCost', 15);
            $table->decimal('AdjustedCost', 15);
            $table->bigInteger('ProcurementMethod')->nullable();
            $table->date('ExpectedDeliveryDate');
            $table->string('SchedulePeriod');
            $table->string('ExecutionStatus');
            $table->string('BudgetLineID')->nullable();
            $table->text('ChangeRemarks')->nullable();
            $table->boolean('IsDeleted')->default(false);
            $table->bigInteger('CreatedBy');
            $table->bigInteger('ModifiedBy');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('CreatedOn');
            $table->dateTime('ModifiedOn');
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('BranchID');
            $table->bigInteger('DepartmentID');
            $table->string('SourceType')->nullable();
            $table->integer('OriginalQTY')->nullable();

            $table->primary(['LineItemID'], 'pk__t_planli__8a871bee940fb1c3');
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
