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
        Schema::table('t_Tenders', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CurrencyId'])->references(['Id'])->on('t_Currencies')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ProcurementModeId'])->references(['id'])->on('t_ProcurementModes')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['RelatedPRID'])->references(['Id'])->on('t_Requisitions')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_Tenders', function (Blueprint $table) {
            $table->dropForeign('t_tenders_createdby_foreign');
            $table->dropForeign('t_tenders_currencyid_foreign');
            $table->dropForeign('t_tenders_deletedby_foreign');
            $table->dropForeign('t_tenders_modifiedby_foreign');
            $table->dropForeign('t_tenders_procurementmodeid_foreign');
            $table->dropForeign('t_tenders_relatedprid_foreign');
        });
    }
};
