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
        Schema::create('t_BudgetGLMasterAllocations', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('BudgetID');
            $table->bigInteger('BranchID');
            $table->bigInteger('GLAttachmentID');
            $table->string('AccountID');
            $table->string('Type')->default('monthly');
            $table->string('Description')->nullable();
            $table->string('GLAccountTypeID', 10);
            $table->decimal('Month1', 15)->default(0);
            $table->decimal('Month2', 15)->default(0);
            $table->decimal('Month3', 15)->default(0);
            $table->decimal('Month4', 15)->default(0);
            $table->decimal('Month5', 15)->default(0);
            $table->decimal('Month6', 15)->default(0);
            $table->decimal('Month7', 15)->default(0);
            $table->decimal('Month8', 15)->default(0);
            $table->decimal('Month9', 15)->default(0);
            $table->decimal('Month10', 15)->default(0);
            $table->decimal('Month11', 15)->default(0);
            $table->decimal('Month12', 15)->default(0);
            $table->decimal('Total', 15)->default(0);
            $table->boolean('IsBeingEdited')->default(false);
            $table->bigInteger('IsBeingEditedBy')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->decimal('Actuals', 15)->default(0);

            $table->primary(['Id'], 'pk__t_budget__3214ec074faec68c');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_BudgetGLMasterAllocations');
    }
};
