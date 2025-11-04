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
        Schema::create('t_BudgetMonthlyProjectionsAllocation', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('BudgetID');
            $table->bigInteger('BudgetProjectionID');
            $table->string('Month');
            $table->decimal('Allocation', 15)->default(0);
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_budget__3214ec072a8f8975');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_BudgetMonthlyProjectionsAllocation');
    }
};
