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
        Schema::table('t_SurveyQuestionResponses', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['SurveyQuestionAnswerID'])->references(['Id'])->on('t_SurveyQuestionAnswers')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['SurveyQuestionID'])->references(['Id'])->on('t_SurveyQuestions')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_SurveyQuestionResponses', function (Blueprint $table) {
            $table->dropForeign('t_surveyquestionresponses_createdby_foreign');
            $table->dropForeign('t_surveyquestionresponses_deletedby_foreign');
            $table->dropForeign('t_surveyquestionresponses_modifiedby_foreign');
            $table->dropForeign('t_surveyquestionresponses_surveyquestionanswerid_foreign');
            $table->dropForeign('t_surveyquestionresponses_surveyquestionid_foreign');
        });
    }
};
