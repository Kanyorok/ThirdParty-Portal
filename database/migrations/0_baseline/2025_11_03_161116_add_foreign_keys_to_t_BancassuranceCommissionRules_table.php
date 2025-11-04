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
        Schema::table('t_BancassuranceCommissionRules', function (Blueprint $table) {
            $table->foreign(['AppliesTo'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['PolicyTypeId'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ProductId'])->references(['Id'])->on('t_InsuranceProducts')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BancassuranceCommissionRules', function (Blueprint $table) {
            $table->dropForeign('t_bancassurancecommissionrules_appliesto_foreign');
            $table->dropForeign('t_bancassurancecommissionrules_createdby_foreign');
            $table->dropForeign('t_bancassurancecommissionrules_deletedby_foreign');
            $table->dropForeign('t_bancassurancecommissionrules_modifiedby_foreign');
            $table->dropForeign('t_bancassurancecommissionrules_policytypeid_foreign');
            $table->dropForeign('t_bancassurancecommissionrules_productid_foreign');
        });
    }
};
