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
        Schema::table('t_BudgetLines', function (Blueprint $table) {
            $table->foreign(['BudgetLineCategoryID'])->references(['Id'])->on('t_BudgetLineCategories')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DepartmentID'])->references(['Id'])->on('t_Departments')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BudgetLines', function (Blueprint $table) {
            $table->dropForeign('t_budgetlines_budgetlinecategoryid_foreign');
            $table->dropForeign('t_budgetlines_createdby_foreign');
            $table->dropForeign('t_budgetlines_deletedby_foreign');
            $table->dropForeign('t_budgetlines_departmentid_foreign');
            $table->dropForeign('t_budgetlines_modifiedby_foreign');
        });
    }
};
