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
        Schema::create('t_BudgetActivities', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('BudgetID')->constrained('t_Budgets', 'Id');
            $table->foreignId('BudgetLineID')->constrained('t_BudgetLines', 'Id');
            $table->foreignId('BranchID')->constrained('t_Branches', 'Id');
            $table->foreignId('ActivityID')->constrained('t_BudgetActivityMaster', 'Id');
            $table->longText('Description')->nullable();
            $table->enum('AllocationType', ['monthly', 'full']);
            $table->decimal('FullAllocation', 15, 2)->nullable(); // only for full

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
        Schema::dropIfExists('t_BudgetActivities');
    }
};
