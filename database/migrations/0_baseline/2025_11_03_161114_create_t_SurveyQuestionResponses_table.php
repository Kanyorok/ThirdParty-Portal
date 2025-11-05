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
        Schema::create('t_SurveyQuestionResponses', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('Party');
            $table->string('PartyID', 100)->nullable();
            $table->string('Source');
            $table->string('SourceID', 100)->nullable();
            $table->text('Response')->nullable();
            $table->bigInteger('SurveyQuestionAnswerID')->nullable();
            $table->bigInteger('SurveyQuestionID');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_survey__3214ec075aa94a07');
            $table->index(['Party', 'PartyID']);
            $table->index(['Source', 'SourceID']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_SurveyQuestionResponses');
    }
};
