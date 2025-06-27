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
            $table->id('Id');
            $table->foreignId('BudgetID')->constrained('t_Budgets', 'Id')->onDelete('cascade');
            $table->foreignId('BranchID')->constrained('t_Branches', 'Id')->onDelete('cascade');
            $table->string('AccountID');
            $table->string('Type')->default('monthly');// monthly or quarterly
            $table->string('Description')->nullable();
            $table->string('GLAccountTypeID',10);
            $table->decimal('Month1', 15, 2)->default(0);
            $table->decimal('Month2', 15, 2)->default(0);
            $table->decimal('Month3', 15, 2)->default(0);
            $table->decimal('Month4', 15, 2)->default(0);
            $table->decimal('Month5', 15, 2)->default(0);
            $table->decimal('Month6', 15, 2)->default(0);
            $table->decimal('Month7', 15, 2)->default(0);
            $table->decimal('Month8', 15, 2)->default(0);
            $table->decimal('Month9', 15, 2)->default(0);
            $table->decimal('Month10', 15, 2)->default(0);
            $table->decimal('Month11', 15, 2)->default(0);
            $table->decimal('Month12', 15, 2)->default(0);
            $table->decimal('Total', 15, 2)->default(0);

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
        Schema::dropIfExists('t_BudgetGLMasterAllocations');
    }
};
