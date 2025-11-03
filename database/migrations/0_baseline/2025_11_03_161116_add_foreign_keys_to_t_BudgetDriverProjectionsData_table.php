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
        Schema::table('t_BudgetDriverProjectionsData', function (Blueprint $table) {
            $table->foreign(['BudgetDriverProjectionsID'])->references(['Id'])->on('t_BudgetDriverProjections')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ProductID'])->references(['Id'])->on('t_BudgetProductTypes')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BudgetDriverProjectionsData', function (Blueprint $table) {
            $table->dropForeign('t_budgetdriverprojectionsdata_budgetdriverprojectionsid_foreign');
            $table->dropForeign('t_budgetdriverprojectionsdata_createdby_foreign');
            $table->dropForeign('t_budgetdriverprojectionsdata_deletedby_foreign');
            $table->dropForeign('t_budgetdriverprojectionsdata_modifiedby_foreign');
            $table->dropForeign('t_budgetdriverprojectionsdata_productid_foreign');
        });
    }
};
