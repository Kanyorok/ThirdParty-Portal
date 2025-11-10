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
        Schema::create('t_BudgetProjectionsData', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('BudgetID')->constrained('t_Budgets', 'Id');
            $table->foreignId('BudgetProjectionID')->constrained('t_BudgetProjections', 'Id');
            $table->bigInteger('ProductID');
            $table->unsignedTinyInteger('Month'); // 1 - 12
            $table->decimal('Amount', 18, 2)->default(0.00);

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
        Schema::dropIfExists('t_BudgetProjectionsData');
    }
};
