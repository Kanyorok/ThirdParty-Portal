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
        Schema::create('t_BudgetLines', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('BudgetLineCategoryID');
            $table->string('LineName');
            $table->bigInteger('DepartmentID');
            $table->string('GLAccountTypeID');
            $table->bigInteger('GLAccountSubTypeID');
            $table->text('Description');
            $table->boolean('IsDefault')->default(false);
            $table->boolean('IsProductDriven')->default(false);
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_budget__3214ec07ec981f8b');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_BudgetLines');
    }
};
