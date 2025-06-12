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
        Schema::create('t_BudgetScenarioPlanning', function (Blueprint $table) {
            $table->id('Id');
            $table->string('scenarioName');
            $table->text('description');
            $table->foreignId('budgetPeriod')->constrained('t_BudgetPeriods', 'Id');
            $table->foreignId('planningMethod')->constrained('t_BudgetPlanningMethods', 'Id');
            $table->boolean('isDefault')->default(false);

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
        Schema::dropIfExists('t_BudgetScenarioPlanning');
    }
};
