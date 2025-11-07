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
            $table->bigIncrements('Id');
            $table->bigInteger('RFQEvaluationId');
            $table->bigInteger('EvaluationId');
            $table->dateTime('CreatedOn')->nullable();
            $table->dateTime('ModifiedOn')->nullable();

            $table->primary(['Id'], 'pk__t_rfqeva__3214ec075e34b28e');
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
