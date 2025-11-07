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
        Schema::create('t_BudgetProjectionsData', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('BudgetID');
            $table->bigInteger('BudgetProjectionID');
            $table->bigInteger('ProductID');
            $table->tinyInteger('Month');
            $table->decimal('Amount', 18)->default(0);
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_budget__3214ec079499abae');
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
