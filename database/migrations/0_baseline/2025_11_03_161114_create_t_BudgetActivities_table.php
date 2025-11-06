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
        Schema::create('t_BudgetActivities', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('BudgetID');
            $table->bigInteger('BudgetLineID');
            $table->bigInteger('BranchID');
            $table->bigInteger('ActivityID');
            $table->text('Description')->nullable();
            $table->enum('AllocationType', ['monthly', 'full']);
            $table->decimal('FullAllocation', 15)->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_budget__3214ec0788909a9f');
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
