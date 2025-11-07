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
        Schema::table('t_FinanceTaxJurisdiction', function (Blueprint $table) {
            $table->foreign(['CountryID'])->references(['Id'])->on('t_Countries')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['Currency'])->references(['Id'])->on('t_Currencies')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FinanceTaxJurisdiction', function (Blueprint $table) {
            $table->dropForeign('t_financetaxjurisdiction_countryid_foreign');
            $table->dropForeign('t_financetaxjurisdiction_createdby_foreign');
            $table->dropForeign('t_financetaxjurisdiction_currency_foreign');
            $table->dropForeign('t_financetaxjurisdiction_deletedby_foreign');
            $table->dropForeign('t_financetaxjurisdiction_modifiedby_foreign');
        });
    }
};
