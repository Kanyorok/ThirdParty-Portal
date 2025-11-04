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
        Schema::create('t_SurveyQuestions', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('SurveyQuestionId', 100)->unique();
            $table->char('Type', 2);
            $table->text('Question');
            $table->text('Notes')->nullable();
            $table->bigInteger('SurveyId');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_survey__3214ec07bebdf8ea');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_SurveyQuestions');
    }
};
