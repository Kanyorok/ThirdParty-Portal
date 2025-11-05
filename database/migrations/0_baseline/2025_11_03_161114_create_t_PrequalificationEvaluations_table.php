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
        Schema::create('t_PrequalificationEvaluations', function (Blueprint $table) {
            $table->bigIncrements('EvaluationID');
            $table->bigInteger('ApplicationID');
            $table->bigInteger('EvaluatorID');
            $table->bigInteger('SectionID');
            $table->float('Score')->nullable();
            $table->float('MaxScore')->nullable();
            $table->text('Remarks')->nullable();
            $table->dateTime('EvaluatedOn')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('CriteriaID');

            $table->primary(['EvaluationID'], 'pk__t_prequa__36ae68d3e2c4d698');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_PrequalificationEvaluations');
    }
};
