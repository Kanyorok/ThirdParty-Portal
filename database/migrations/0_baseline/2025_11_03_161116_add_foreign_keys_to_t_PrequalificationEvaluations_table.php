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
        Schema::table('t_PrequalificationEvaluations', function (Blueprint $table) {
            $table->foreign(['ApplicationID'])->references(['ApplicationID'])->on('t_SupplierPrequalificationApplications')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['CriteriaID'])->references(['Id'])->on('t_Criterias')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['EvaluatorID'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['SectionID'])->references(['Id'])->on('t_Sections')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_PrequalificationEvaluations', function (Blueprint $table) {
            $table->dropForeign('t_prequalificationevaluations_applicationid_foreign');
            $table->dropForeign('t_prequalificationevaluations_criteriaid_foreign');
            $table->dropForeign('t_prequalificationevaluations_evaluatorid_foreign');
            $table->dropForeign('t_prequalificationevaluations_sectionid_foreign');
        });
    }
};
