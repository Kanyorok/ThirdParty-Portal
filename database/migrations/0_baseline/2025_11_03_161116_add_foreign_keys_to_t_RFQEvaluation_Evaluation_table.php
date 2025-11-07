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
        Schema::table('t_RFQEvaluation_Evaluation', function (Blueprint $table) {
            $table->foreign(['EvaluationId'])->references(['Id'])->on('t_SupplierResponseEvaluations')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['RFQEvaluationId'])->references(['Id'])->on('t_RFQEvaluations')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_RFQEvaluation_Evaluation', function (Blueprint $table) {
            $table->dropForeign('t_rfqevaluation_evaluation_evaluationid_foreign');
            $table->dropForeign('t_rfqevaluation_evaluation_rfqevaluationid_foreign');
        });
    }
};
