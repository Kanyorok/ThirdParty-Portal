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
        Schema::table('t_ProcurementPlans', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ItemId'])->references(['Id'])->on('t_Items')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ProcurementPeriodId'])->references(['Id'])->on('t_ProcurementPeriods')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_ProcurementPlans', function (Blueprint $table) {
            $table->dropForeign('t_procurementplans_createdby_foreign');
            $table->dropForeign('t_procurementplans_deletedby_foreign');
            $table->dropForeign('t_procurementplans_itemid_foreign');
            $table->dropForeign('t_procurementplans_modifiedby_foreign');
            $table->dropForeign('t_procurementplans_procurementperiodid_foreign');
        });
    }
};
