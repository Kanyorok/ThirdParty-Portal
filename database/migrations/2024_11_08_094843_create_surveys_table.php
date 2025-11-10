<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_Surveys', static function (Blueprint $table) {
            $table->id('Id');
            $table->string('SurveyID', 100)->unique()->index();
            $table->string('Label', 250);
            $table->longText('Notes')->nullable();
            $table->dateTime('StartOn');
            $table->dateTime('EndOn');
            $table->char('Status', 2);
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        Schema::create('t_SurveyQuestions', static function (Blueprint $table) {
            $table->id('Id');
            $table->string('SurveyQuestionId', 100)->unique()->index();
            $table->char('Type', 2);
            $table->longText('Question');
            $table->longText('Notes')->nullable();
            $table->foreignId('SurveyId')->constrained('t_Surveys', 'Id');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        Schema::create('t_SurveyQuestionAnswers', static function (Blueprint $table) {
            $table->id('Id');
            $table->longText('Answer');
            $table->longText('Notes')->nullable();
            $table->foreignId('SurveyQuestionID')->constrained('t_SurveyQuestions', 'Id');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        Schema::create('t_SurveyQuestionResponses', static function (Blueprint $table) {
            $table->id('Id');
            $table->string("Party");
            $table->string("PartyID", 100)->nullable();
            $table->string("Source");//string or related.
            $table->string("SourceID", 100)->nullable();
            $table->longText('Response')->nullable();
            $table->foreignId('SurveyQuestionAnswerID')->nullable()->constrained('t_SurveyQuestionAnswers', 'Id');
            $table->foreignId('SurveyQuestionID')->constrained('t_SurveyQuestions', 'Id');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');

            $table->index(["Source", "SourceID"]);
            $table->index(["Party", "PartyID"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_SurveyQuestionResponses');
        Schema::dropIfExists('t_SurveyQuestionAnswers');
        Schema::dropIfExists('t_SurveyQuestions');
        Schema::dropIfExists('t_Surveys');
    }
};
