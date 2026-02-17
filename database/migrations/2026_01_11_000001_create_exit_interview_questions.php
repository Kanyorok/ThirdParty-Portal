<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_HRExitInterviewQuestions', function (Blueprint $table) {
            $table->id('Id');
            $table->string('Question', 255);
            $table->integer('Sequence')->default(1);
            $table->boolean('IsActive')->default(1);
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
        });

        Schema::create('t_HRExitInterviewResponses', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('ExitID');
            $table->unsignedBigInteger('QuestionID');
            $table->text('Answer')->nullable();
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->unique(['ExitID', 'QuestionID'], 'UQ_HRExitInterviewResponses_Exit_Question');
            $table->foreign('ExitID')->references('Id')->on('t_HRExitRequests');
            $table->foreign('QuestionID')->references('Id')->on('t_HRExitInterviewQuestions');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HRExitInterviewResponses');
        Schema::dropIfExists('t_HRExitInterviewQuestions');
    }
};
