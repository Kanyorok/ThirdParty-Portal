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
        Schema::table('t_RFQSupplierResponseEvaluations', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CriteriaId'])->references(['id'])->on('t_RFQCriteria')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['RFQEvaluationId'])->references(['Id'])->on('t_RFQEvaluations')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['SupplierId'])->references(['Id'])->on('t_Suppliers')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_RFQSupplierResponseEvaluations', function (Blueprint $table) {
            $table->dropForeign('t_rfqsupplierresponseevaluations_createdby_foreign');
            $table->dropForeign('t_rfqsupplierresponseevaluations_criteriaid_foreign');
            $table->dropForeign('t_rfqsupplierresponseevaluations_deletedby_foreign');
            $table->dropForeign('t_rfqsupplierresponseevaluations_modifiedby_foreign');
            $table->dropForeign('t_rfqsupplierresponseevaluations_rfqevaluationid_foreign');
            $table->dropForeign('t_rfqsupplierresponseevaluations_supplierid_foreign');
        });
    }
};
