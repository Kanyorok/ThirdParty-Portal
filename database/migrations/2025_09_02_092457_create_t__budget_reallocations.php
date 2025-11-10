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
        Schema::create('t_BudgetReallocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('BudgetID')->constrained('t_Budgets');
            $table->foreignId('FromBudgetLineID')->constrained('t_BudgetLines');
            $table->foreignId('ToBudgetLineID')->constrained('t_BudgetLines');
            $table->foreignId('FromActivityID')->nullable()->constrained('t_BudgetActivityMaster');
            $table->foreignId('ToActivityID')->nullable()->constrained('t_BudgetActivityMaster');
            $table->foreignId('BranchID')->nullable()->constrained('t_Branches');
            $table->foreignId('DepartmentID')->nullable()->constrained('t_Departments');
            $table->string('ReallocationType'); // Branch, Dept, Cross-Dept
            $table->decimal('Amount', 15, 2);
            $table->text('Justification');
            $table->string('Status')->default('pending');
            $table->foreignId('CreatedBy')->constrained('t_Users');
            $table->timestamp('CreatedOn')->useCurrent();
            $table->foreignId('ApprovedBy')->nullable()->constrained('t_Users');
            $table->timestamp('ApprovedOn')->nullable();
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
