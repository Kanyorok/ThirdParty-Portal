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
        Schema::create('t_RFQEvaluation_Evaluation', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('RFQEvaluationId');
            $table->unsignedBigInteger('EvaluationId'); // Refers to SupplierResponseEvaluation
            $table->dateTime('CreatedOn')->nullable()->comment('Timestamp when the record was created');
            $table->dateTime('ModifiedOn')->nullable()->comment('Timestamp when the record was last modified');
        
            $table->foreign('RFQEvaluationId')->references('id')->on('t_RFQEvaluations')->onDelete('cascade');
            $table->foreign('EvaluationId')->references('id')->on('t_SupplierResponseEvaluations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_RFQEvaluation_Evaluation');
    }
};
