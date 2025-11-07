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
        Schema::create('t_BudgetReallocations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('BudgetID');
            $table->bigInteger('FromBudgetLineID');
            $table->bigInteger('ToBudgetLineID');
            $table->bigInteger('FromActivityID')->nullable();
            $table->bigInteger('ToActivityID')->nullable();
            $table->bigInteger('BranchID')->nullable();
            $table->bigInteger('DepartmentID')->nullable();
            $table->string('ReallocationType');
            $table->decimal('Amount', 15);
            $table->text('Justification');
            $table->string('Status')->default('pending');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn')->useCurrent();
            $table->bigInteger('ApprovedBy')->nullable();
            $table->dateTime('ApprovedOn')->nullable();
            $table->text('ApprovalReason')->nullable();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['id'], 'pk__t_budget__3213e83fd5912d77');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_BudgetReallocations');
    }
};
