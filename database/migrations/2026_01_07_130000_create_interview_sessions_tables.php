<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_HRInterviewSessions', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('JobOpeningID');
            $table->integer('RoundNo')->default(1);
            $table->string('RoundLabel', 50)->nullable();
            $table->string('InterviewType', 100)->nullable();
            $table->date('InterviewDate')->nullable();
            $table->string('Location', 150)->nullable();
            $table->string('Status', 30)->default('Scheduled');
            $table->text('Notes')->nullable();

            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();

            $table->foreign('JobOpeningID')->references('Id')->on('t_HRJobOpenings');
        });

        Schema::create('t_HRInterviewSessionCandidates', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('SessionID');
            $table->unsignedBigInteger('ApplicationID');
            $table->dateTime('SlotTime')->nullable();
            $table->string('Status', 30)->default('Scheduled');
            $table->decimal('Score', 10, 2)->nullable();
            $table->string('Recommendation', 30)->nullable();
            $table->text('Notes')->nullable();

            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();

            $table->foreign('SessionID')->references('Id')->on('t_HRInterviewSessions')->onDelete('cascade');
            $table->foreign('ApplicationID')->references('Id')->on('t_HRJobApplications');
        });

        Schema::create('t_HRInterviewSessionPanels', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('SessionID');
            $table->unsignedBigInteger('EmployeeID');
            $table->string('Role', 100)->nullable();
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();

            $table->foreign('SessionID')->references('Id')->on('t_HRInterviewSessions')->onDelete('cascade');
            $table->foreign('EmployeeID')->references('Id')->on('t_HREmployees');
        });

        Schema::create('t_HRInterviewSessionQuestions', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('SessionID');
            $table->unsignedBigInteger('QuestionID');
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();

            $table->foreign('SessionID')->references('Id')->on('t_HRInterviewSessions')->onDelete('cascade');
            $table->foreign('QuestionID')->references('Id')->on('t_HRJobInterviewQuestions')->onDelete('cascade');
        });

        Schema::create('t_HRInterviewSessionQuestionAssignments', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('SessionID');
            $table->unsignedBigInteger('QuestionID');
            $table->unsignedBigInteger('PanelistID')->nullable();
            $table->unsignedBigInteger('AssignedBy')->nullable();
            $table->dateTime('AssignedOn')->useCurrent();

            $table->foreign('SessionID')->references('Id')->on('t_HRInterviewSessions')->onDelete('cascade');
            $table->foreign('QuestionID')->references('Id')->on('t_HRJobInterviewQuestions')->onDelete('cascade');
            $table->foreign('PanelistID')->references('Id')->on('t_HREmployees');
        });

        Schema::create('t_HRInterviewSessionScores', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('SessionCandidateID');
            $table->unsignedBigInteger('QuestionID');
            $table->unsignedBigInteger('PanelistID')->nullable();
            $table->decimal('Score', 10, 2)->nullable();
            $table->text('Comment')->nullable();
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();

            $table->foreign('SessionCandidateID')->references('Id')->on('t_HRInterviewSessionCandidates')->onDelete('cascade');
            $table->foreign('QuestionID')->references('Id')->on('t_HRJobInterviewQuestions')->onDelete('cascade');
            $table->foreign('PanelistID')->references('Id')->on('t_HREmployees');
        });

        Schema::create('t_HRInterviewSessionNotifications', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('SessionID');
            $table->unsignedBigInteger('EmployeeID');
            $table->string('Message', 255);
            $table->dateTime('CreatedOn')->useCurrent();

            $table->foreign('SessionID')->references('Id')->on('t_HRInterviewSessions')->onDelete('cascade');
            $table->foreign('EmployeeID')->references('Id')->on('t_HREmployees');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HRInterviewSessionNotifications');
        Schema::dropIfExists('t_HRInterviewSessionScores');
        Schema::dropIfExists('t_HRInterviewSessionQuestionAssignments');
        Schema::dropIfExists('t_HRInterviewSessionQuestions');
        Schema::dropIfExists('t_HRInterviewSessionPanels');
        Schema::dropIfExists('t_HRInterviewSessionCandidates');
        Schema::dropIfExists('t_HRInterviewSessions');
    }
};
