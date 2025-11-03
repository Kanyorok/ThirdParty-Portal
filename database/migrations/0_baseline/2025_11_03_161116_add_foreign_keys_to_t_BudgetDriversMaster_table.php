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
        Schema::table('t_BudgetDriversMaster', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DriverTypeID'])->references(['Id'])->on('t_BudgetDrivers')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BudgetDriversMaster', function (Blueprint $table) {
            $table->dropForeign('t_budgetdriversmaster_createdby_foreign');
            $table->dropForeign('t_budgetdriversmaster_deletedby_foreign');
            $table->dropForeign('t_budgetdriversmaster_drivertypeid_foreign');
            $table->dropForeign('t_budgetdriversmaster_modifiedby_foreign');
        });
    }
};
