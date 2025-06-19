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
        Schema::create('t_BudgetLines', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('BudgetLineCategoryID')->constrained('t_BudgetLineCategories', 'Id');
            $table->string('LineName');
            $table->foreignId('DepartmentID')->constrained('t_Departments', 'Id');
            $table->string('GLAccountTypeID');
            $table->foreignId('GLAccountSubTypeID')->constrained('t_GLAccountSubTypes', 'Id');
            $table->text('Description');
            $table->boolean('IsDefault')->default(false);
            $table->boolean('IsProductDriven')->default(false);
            
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
        Schema::dropIfExists('t_BudgetLines');
    }
};
