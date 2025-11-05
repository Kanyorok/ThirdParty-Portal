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
        Schema::create('t_BudgetLineLink', function (Blueprint $table) {
            $table->bigIncrements('LinkID');
            $table->bigInteger('LineItemID');
            $table->string('BudgetLineID');
            $table->decimal('AmountAllocated', 15)->default(0);
            $table->bigInteger('LinkedBy');
            $table->dateTime('LinkedDate')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['LinkID'], 'pk__t_budget__2d122155f045f914');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_BudgetLineLink');
    }
};
